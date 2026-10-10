<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Redirect;
use App\Support\UrlPath;
use SimpleXMLElement;

/**
 * Imports published pages and posts from a WordPress WXR export, plus Rank Math redirects.
 *
 * Canonical paths come straight from the exported <link>, so the live slash / no-slash
 * forms are preserved exactly. Drafts are skipped.
 */
class WordPressImporter
{
    private const NS = [
        'wp' => 'http://wordpress.org/export/1.2/',
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'excerpt' => 'http://wordpress.org/export/1.2/excerpt/',
    ];

    /** @var array<string,string> */
    public array $report = [];

    /** @var list<string> */
    public array $notes = [];

    /**
     * @param  array<string,array{title?:string,description?:string}>  $snapshot  keyed by canonical_path
     * @return list<array<string,mixed>>
     */
    public function parsePages(string $xmlPath, array $snapshot = []): array
    {
        $xml = new SimpleXMLElement(file_get_contents($xmlPath));
        $channel = $xml->channel;
        $site = rtrim((string) $channel->link, '/');

        $attachments = [];
        foreach ($channel->item as $item) {
            $wp = $item->children(self::NS['wp']);
            if ((string) $wp->post_type === 'attachment') {
                $attachments[(string) $wp->post_id] = $this->relative((string) $wp->attachment_url, $site);
            }
        }

        $site_name = (string) $channel->title;
        $rows = [];

        foreach ($channel->item as $item) {
            $wp = $item->children(self::NS['wp']);
            $type = (string) $wp->post_type;

            if (! in_array($type, ['page', 'post'], true) || (string) $wp->status !== 'publish') {
                continue;
            }

            $meta = [];
            foreach ($wp->postmeta as $m) {
                $meta[(string) $m->meta_key] = (string) $m->meta_value;
            }

            $path = $this->pathOf((string) $item->link, $site);
            $body = (string) $item->children(self::NS['content'])->encoded;
            [$h1, $body] = $this->splitH1($body);
            $title = html_entity_decode((string) $item->title, ENT_QUOTES);

            $snap = $snapshot[$path] ?? [];
            $seoTitle = $meta['rank_math_title'] ?? ($snap['title'] ?? null);
            $desc = $meta['rank_math_description'] ?? ($snap['description'] ?? null);

            $rows[] = [
                'kind' => $type,
                'type' => $this->classify($path, $type),
                'template' => 'standard',
                'canonical_path' => $path,
                'title' => $title,
                'h1' => $h1 ?: $title,
                'body_html' => $body,
                'excerpt' => trim((string) $item->children(self::NS['excerpt'])->encoded) ?: null,
                'featured_image' => isset($meta['_thumbnail_id']) ? ($attachments[$meta['_thumbnail_id']] ?? null) : null,
                'seo_title' => $seoTitle ?: null,
                'meta_description' => $desc ?: null,
                'robots' => $this->robots($meta['rank_math_robots'] ?? null),
                'focus_keyword' => $meta['rank_math_focus_keyword'] ?? null,
                'status' => Page::STATUS_PUBLISHED,
                'published_at' => (string) $wp->post_date_gmt ?: null,
                'wp_modified_at' => (string) $wp->post_modified_gmt ?: null,
                'wp_id' => (int) $wp->post_id,
                '_custom_permalink' => $meta['custom_permalink'] ?? null,
                '_needs_snapshot' => ! $seoTitle || ! $desc,
                '_site_name' => $site_name,
            ];
        }

        return $rows;
    }

    /**
     * Resolve targets to canonical paths and flatten chains.
     *
     * @param  list<array{from:string,to:?string,status:int,hits?:int}>  $raw
     * @param  array<string,string>  $pathByKey  redirectKey => canonical_path of a page (incl. custom_permalink aliases)
     * @return list<array<string,mixed>>
     */
    public function buildRedirects(array $raw, array $pathByKey): array
    {
        $rules = [];
        foreach ($raw as $r) {
            if (str_contains($r['from'], '…') || str_contains($r['from'], '[')) {
                $this->notes[] = "Skipped truncated source: {$r['from']}";

                continue;
            }
            $key = UrlPath::redirectKey($r['from']);
            $rules[$key] = $r + ['note' => null];
        }

        $out = [];
        foreach ($rules as $key => $r) {
            $status = (int) $r['status'];
            $note = null;
            $to = null;

            if ($status !== 410) {
                $to = $r['to'] ?? '/';
                $hops = 0;
                while ($hops++ < 10) {
                    if (! str_starts_with($to, '/')) {
                        break;
                    }
                    $tk = UrlPath::redirectKey(parse_url($to, PHP_URL_PATH) ?: '/');
                    if (isset($rules[$tk]) && (int) $rules[$tk]['status'] !== 410 && $tk !== $key) {
                        $to = $rules[$tk]['to'] ?? '/';
                        $note = 'Flattened chain';

                        continue;
                    }
                    if (isset($pathByKey[$tk])) {
                        $to = $pathByKey[$tk];
                    }
                    break;
                }

                if (str_starts_with($to, '/') && $to !== '/') {
                    $tk = UrlPath::redirectKey($to);
                    if (! isset($pathByKey[$tk]) && ! str_starts_with($to, '/wp-content/')) {
                        $note = trim(($note ? $note.'. ' : '').'Target is not a published page');
                        $this->notes[] = "Dangling target: {$r['from']} -> {$to}";
                    }
                }
            }

            $out[] = [
                'from_path' => $key,
                'to_path' => $status === 410 ? null : $to,
                'status_code' => $status,
                'hits' => (int) ($r['hits'] ?? 0),
                'is_active' => true,
                'note' => $note,
            ];
        }

        return $out;
    }

    public function persist(array $pages, array $redirects): void
    {
        foreach ($pages as $row) {
            $row = array_filter($row, fn ($k) => ! str_starts_with($k, '_'), ARRAY_FILTER_USE_KEY);
            Page::updateOrCreate(['canonical_path' => $row['canonical_path']], $row);
        }
        foreach ($redirects as $row) {
            Redirect::updateOrCreate(['from_path' => $row['from_path']], $row);
        }
    }

    private function pathOf(string $link, string $site): string
    {
        $path = parse_url($link, PHP_URL_PATH) ?: '/';

        return '/'.ltrim($path, '/');
    }

    private function relative(string $url, string $site): string
    {
        return str_starts_with($url, $site) ? substr($url, strlen($site)) : $url;
    }

    /** @return array{0:?string,1:string} */
    private function splitH1(string $html): array
    {
        if (preg_match('#<h1[^>]*>(.*?)</h1>#is', $html, $m)) {
            $h1 = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES));
            $html = preg_replace('#<h1[^>]*>.*?</h1>#is', '', $html, 1);

            return [$h1, trim($html)];
        }

        return [null, trim($html)];
    }

    private function robots(?string $serialized): ?string
    {
        if (! $serialized) {
            return null;
        }

        return str_contains($serialized, '"noindex"') ? 'noindex, follow' : null;
    }

    private function classify(string $path, string $kind): string
    {
        return match (true) {
            $path === '/' => 'home',
            $kind === 'post' => 'post',
            str_starts_with($path, '/service-area/') => 'suburb',
            $path === '/blog/' => 'blog_index',
            (bool) preg_match('#thank-you|login|dont-try|booking-form#', $path) => 'utility',
            (bool) preg_match('#terms|privacy|cookie|disclaimer#', $path) => 'legal',
            default => 'page',
        };
    }
}

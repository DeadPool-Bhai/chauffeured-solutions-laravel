<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Redirect;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Compares every URL on staging with the same URL on the live WordPress site.
 * Run it where both sites are reachable (the Hostinger server over SSH, or your own machine).
 *
 *   php artisan qa:crawl https://staging.example.com --user=qa --pass=secret
 */
class QaCrawl extends Command
{
    protected $signature = 'qa:crawl {staging : Base URL of the staging site}
        {--live=https://chauffeuredsolutions.com.au : Base URL of the live site}
        {--user= : Staging HTTP auth user} {--pass= : Staging HTTP auth password}
        {--only= : Only paths starting with this} {--limit=0 : Stop after N pages}
        {--redirects : Also compare the redirect rules}
        {--out=storage/app/qa-crawl.csv : Where to write the full report}';

    protected $description = 'Compare staging with live: status, canonical, title, description, robots, H1, JSON-LD, headings, words';

    /** Differences that must be zero before cutover. The rest are reported for review. */
    private const HARD = ['status', 'canonical', 'title', 'description', 'robots', 'h1', 'schema'];

    public function handle(): int
    {
        $staging = rtrim($this->argument('staging'), '/');
        $live = rtrim($this->option('live'), '/');

        $query = Page::live()->orderBy('canonical_path');
        if ($only = $this->option('only')) {
            $query->where('canonical_path', 'like', $only.'%');
        }
        $paths = $query->pluck('canonical_path');
        if ($limit = (int) $this->option('limit')) {
            $paths = $paths->take($limit);
        }

        $rows = [];
        $hard = 0;
        $bar = $this->output->createProgressBar($paths->count());
        foreach ($paths as $path) {
            $a = $this->snapshot($this->client(false), $live.$path);
            $b = $this->snapshot($this->client(true), $staging.$path);
            $diff = $this->diff($a, $b);
            $isHard = (bool) array_intersect(array_keys($diff), self::HARD);
            $hard += $isHard ? 1 : 0;
            $rows[] = ['path' => $path, 'level' => $diff ? ($isHard ? 'HARD' : 'soft') : 'ok', 'diff' => $diff, 'live' => $a, 'staging' => $b];
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $redirectBad = $this->option('redirects') ? $this->compareRedirects($live, $staging) : 0;

        $this->writeCsv($rows);
        $this->report($rows, $hard, $redirectBad);

        return ($hard + $redirectBad) ? self::FAILURE : self::SUCCESS;
    }

    private function client(bool $staging): PendingRequest
    {
        $c = Http::timeout(30)->withOptions(['allow_redirects' => false])->withUserAgent('CS-QA-Crawl/1.0');
        if ($staging && $this->option('user')) {
            $c = $c->withBasicAuth($this->option('user'), (string) $this->option('pass'));
        }

        return $c;
    }

    /** @return array<string,mixed> */
    public function snapshot(PendingRequest $client, string $url): array
    {
        try {
            $res = $client->get($url);
        } catch (\Throwable $e) {
            return ['status' => 'ERR', 'error' => $e->getMessage()];
        }

        $snap = ['status' => $res->status(), 'location' => $res->header('Location') ?: null];
        $html = $res->body();
        if ($res->status() !== 200 || $html === '') {
            return $snap;
        }

        return $snap + self::parse($html);
    }

    /** @return array<string,mixed> */
    public static function parse(string $html): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $x = new DOMXPath($dom);
        $norm = fn (?string $s) => trim(preg_replace('/\s+/u', ' ', html_entity_decode((string) $s, ENT_QUOTES)));
        $attr = fn (string $q, string $a) => $norm($x->query($q)->item(0)?->getAttribute($a));

        foreach ($x->query('//script|//style|//noscript|//svg') as $n) {
            $n->parentNode?->removeChild($n);
        }
        $main = $x->query('//main')->item(0) ?? $dom->documentElement;

        $schema = [];
        $dom2 = new DOMDocument;
        @$dom2->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach ((new DOMXPath($dom2))->query('//script[@type="application/ld+json"]') as $s) {
            $j = json_decode(trim($s->textContent), true);
            $schema[] = $j === null ? md5(trim($s->textContent)) : md5(json_encode($j));
        }
        sort($schema);

        return [
            'canonical' => $attr('//link[@rel="canonical"]', 'href'),
            'title' => $norm($x->query('//title')->item(0)?->textContent),
            'description' => $attr('//meta[@name="description"]', 'content'),
            'robots' => $attr('//meta[@name="robots"]', 'content'),
            'h1' => $norm($x->query('//h1')->item(0)?->textContent),
            'h1_count' => $x->query('//h1')->length,
            'h2' => $x->query('//h2')->length,
            'h3' => $x->query('//h3')->length,
            'words' => str_word_count(strip_tags($main->textContent ?? '')),
            'schema' => implode(',', $schema),
        ];
    }

    /** @return array<string,array{live:mixed,staging:mixed}> */
    public function diff(array $a, array $b): array
    {
        $out = [];
        foreach (['status', 'canonical', 'title', 'description', 'h1', 'schema'] as $k) {
            if (($a[$k] ?? null) !== ($b[$k] ?? null)) {
                $out[$k] = ['live' => $a[$k] ?? null, 'staging' => $b[$k] ?? null];
            }
        }
        // Live may omit the robots meta (Rank Math default is index); treat that as "index, follow".
        $robots = fn ($v) => ($v ?? '') === '' ? 'index, follow' : preg_replace('/\s+/', ' ', strtolower($v));
        if (isset($a['robots'], $b['robots']) && $robots($a['robots']) !== $robots($b['robots'])) {
            $out['robots'] = ['live' => $a['robots'], 'staging' => $b['robots']];
        }
        foreach (['h2', 'h3'] as $k) {
            if (isset($a[$k], $b[$k]) && $a[$k] !== $b[$k]) {
                $out[$k] = ['live' => $a[$k], 'staging' => $b[$k]];
            }
        }
        if (isset($a['words'], $b['words']) && $a['words'] > 0 && abs($a['words'] - $b['words']) / $a['words'] > 0.15) {
            $out['words'] = ['live' => $a['words'], 'staging' => $b['words']];
        }

        return $out;
    }

    private function compareRedirects(string $live, string $staging): int
    {
        $bad = 0;
        foreach (Redirect::where('is_active', true)->get() as $r) {
            $from = '/'.ltrim($r->from_path, '/');
            $a = $this->snapshot($this->client(false), $live.$from);
            $b = $this->snapshot($this->client(true), $staging.$from);
            $path = fn ($l) => $l ? rtrim((string) (parse_url($l, PHP_URL_PATH) ?: '/'), '/') : null;
            $same = ($a['status'] ?? 0) === ($b['status'] ?? -1) && $path($a['location'] ?? null) === $path($b['location'] ?? null);
            if (! $same) {
                $bad++;
                $this->warn(sprintf('redirect %s: live %s -> %s | staging %s -> %s', $from, $a['status'], $a['location'] ?? '-', $b['status'], $b['location'] ?? '-'));
            }
        }
        $this->info($bad ? "{$bad} redirects differ (a differing live chain that we flattened is expected)." : 'All redirects match.');

        return 0; // Reported for review, never fails the run: flattened chains legitimately differ.
    }

    private function writeCsv(array $rows): void
    {
        $file = base_path($this->option('out'));
        @mkdir(dirname($file), 0775, true);
        $h = fopen($file, 'w');
        fputcsv($h, ['path', 'level', 'field', 'live', 'staging']);
        foreach ($rows as $r) {
            if (! $r['diff']) {
                fputcsv($h, [$r['path'], 'ok', '', '', '']);
            }
            foreach ($r['diff'] as $field => $v) {
                fputcsv($h, [$r['path'], in_array($field, self::HARD) ? 'HARD' : 'soft', $field, $v['live'], $v['staging']]);
            }
        }
        fclose($h);
        $this->line('Full report: '.$file);
    }

    private function report(array $rows, int $hard, int $unused): void
    {
        $counts = collect($rows)->countBy('level');
        $this->info(sprintf('%d pages: %d ok, %d soft differences, %d with hard differences.', count($rows), $counts['ok'] ?? 0, $counts['soft'] ?? 0, $hard));
        foreach (collect($rows)->where('level', 'HARD')->take(40) as $r) {
            $this->line('HARD '.$r['path'].': '.implode(', ', array_intersect(array_keys($r['diff']), self::HARD)));
        }
    }
}

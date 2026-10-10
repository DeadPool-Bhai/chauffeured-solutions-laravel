<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use App\Support\UrlPath;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $entries = collect(['post', 'page'])->map(fn ($kind) => [
            'loc' => $this->abs("/{$kind}-sitemap.xml"),
            'lastmod' => $this->lastmod($this->pages($kind)),
        ]);

        return $this->xml(view('sitemap.index', ['entries' => $entries])->render());
    }

    public function show(string $kind): Response
    {
        abort_unless(in_array($kind, ['page', 'post'], true), 404);

        $urls = $this->pages($kind)->map(fn (Page $p) => [
            'loc' => $this->abs($p->canonical_path),
            'lastmod' => ($p->wp_modified_at ?? $p->updated_at)?->toAtomString(),
        ]);

        return $this->xml(view('sitemap.urlset', ['urls' => $urls])->render());
    }

    public function robots(): Response
    {
        $admin = '/'.trim(env('ADMIN_PATH', 'admin'), '/').'/';

        $body = implode("\n", [
            'User-agent: *',
            "Disallow: {$admin}",
            'Disallow: /lead/',
            '',
            'Sitemap: '.$this->abs('/sitemap_index.xml'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Published, indexable pages of one kind. Anything a redirect rule takes over is left out,
     * because a sitemap should only list URLs that return 200.
     *
     * @return Collection<int,Page>
     */
    public function pages(string $kind): Collection
    {
        $redirected = Redirect::where('is_active', true)->pluck('from_path')->flip();

        return Page::live()
            ->where('kind', $kind)
            ->where(fn ($q) => $q->whereNull('robots')->orWhere('robots', 'not like', '%noindex%'))
            ->orderBy('id')
            ->get()
            ->reject(fn (Page $p) => isset($redirected[UrlPath::redirectKey($p->canonical_path)]) || in_array($p->canonical_path, config('seo.sitemap_exclude'), true))
            ->values();
    }

    private function abs(string $path): string
    {
        return rtrim(config('app.url'), '/').$path;
    }

    private function lastmod(Collection $pages): ?string
    {
        return $pages->map(fn (Page $p) => $p->wp_modified_at ?? $p->updated_at)->filter()->max()?->toAtomString();
    }

    private function xml(string $body): Response
    {
        return response($body, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}

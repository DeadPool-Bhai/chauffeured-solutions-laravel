<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use App\Support\UrlPath;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PageController extends Controller
{
    /**
     * Order of resolution:
     * 1. Redirect rules (they beat pages, e.g. /corporate-sedan/).
     * 2. Exact canonical_path match on a published page (200).
     * 3. Trailing-slash twin of a published page (301 to the canonical form).
     * 4. 404.
     */
    public function show(Request $request): SymfonyResponse
    {
        $path = UrlPath::fromRequestPath(rawurldecode($request->getPathInfo()));

        if ($response = $this->redirectFor($request, $path)) {
            return $response;
        }

        $page = Page::live()->where('canonical_path', $path)->first();

        if ($page) {
            $view = view()->exists('pages.'.$page->type) ? 'pages.'.$page->type : 'pages.show';

            return response()->view($view, ['page' => $page->load('faqs')]);
        }

        if (($twin = UrlPath::twin($path)) !== null) {
            $twinPage = Page::live()->where('canonical_path', $twin)->first();

            if ($twinPage) {
                return redirect($this->withQuery($twinPage->canonical_path, $request), 301);
            }
        }

        abort(404);
    }

    private function redirectFor(Request $request, string $path): ?SymfonyResponse
    {
        $rule = Redirect::where('from_path', UrlPath::redirectKey($path))
            ->where('is_active', true)
            ->first();

        if (! $rule) {
            return null;
        }

        $rule->increment('hits', 1, ['last_hit_at' => now()]);

        if ($rule->isGone()) {
            return new Response('Gone', 410);
        }

        $target = $rule->to_path ?: '/';
        $target = preg_match('#^https?://#i', $target) ? $target : $this->withQuery($target, $request);

        return redirect($target, $rule->status_code ?: 301);
    }

    private function withQuery(string $path, Request $request): string
    {
        $qs = $request->getQueryString();

        return $qs ? $path.(str_contains($path, '?') ? '&' : '?').$qs : $path;
    }
}

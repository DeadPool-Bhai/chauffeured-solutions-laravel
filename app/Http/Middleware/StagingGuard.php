<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** On staging: keep search engines out and optionally ask for a shared login. No effect in production. */
class StagingGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('site.staging')) {
            return $next($request);
        }

        $auth = (string) config('site.staging_auth');
        if ($auth !== '' && ! $request->is('up')) {
            $given = $request->getUser().':'.$request->getPassword();
            if (! hash_equals($auth, $given)) {
                return response('Authentication required.', 401, [
                    'WWW-Authenticate' => 'Basic realm="Staging"',
                    'X-Robots-Tag' => 'noindex, nofollow',
                ]);
            }
        }

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}

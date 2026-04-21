<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    /**
     * Prevent browser from caching authenticated pages.
     *
     * Without this, pressing the browser "Back" button after logout
     * will display the previously cached page — even though the
     * session has already been invalidated. This is a security risk
     * because another user on the same browser could see stale data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}

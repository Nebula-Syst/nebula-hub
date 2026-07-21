<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class DisableCookies
{

    public function handle(Request $request, Closure $next)
    {
        $sessionCookieName = str_replace(' ', '_', strtolower(config('app.name')).'_session');
        $cookiesAlreadySet = $request->hasCookie($sessionCookieName) || $request->hasCookie('nebula-hub-xsrf-token');

        if ($cookiesAlreadySet) {
            return $next($request);
        }

        Cookie::queue(Cookie::forget($sessionCookieName));
        Cookie::queue(Cookie::forget('nebula-hub-xsrf-token'));
        config(['session.driver' => 'array']);

        $response = $next($request);
        $response->headers->remove('Set-Cookie');

        return $response;
    }
}

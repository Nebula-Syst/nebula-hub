<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Create a new "XSRF-TOKEN" cookie under an app-specific name.
     *
     * Nebula Hub shares its parent domain (nebulasyst.com) with other, unrelated
     * apps. Laravel's default cookie name "XSRF-TOKEN" collides with theirs when
     * they set a wider-scoped (.nebulasyst.com) cookie of the same name, causing
     * the browser to send both and the server to sometimes read the wrong one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $config
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function newCookie($request, $config)
    {
        return new Cookie(
            'nebula-hub-xsrf-token',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null
        );
    }

    /**
     * Determine if the cookie contents should be serialized.
     *
     * @return bool
     */
    public static function serialized()
    {
        return EncryptCookies::serialized('nebula-hub-xsrf-token');
    }
}

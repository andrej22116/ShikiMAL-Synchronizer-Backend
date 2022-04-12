<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Cookie;
use Closure;

class CookieAdderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        foreach ( Cookie::instance()->getQueuedCookies() as $cookie ) {
            $response->withCookie($cookie);
        }

        return $response;
    }
}

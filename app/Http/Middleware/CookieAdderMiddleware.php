<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Cookie;
use Closure;
use Illuminate\Http\Request;

class CookieAdderMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next ) {
        $response = $next( $request );

        foreach ( app( Cookie::class )->getQueuedCookies() as $cookie ) {
            $response->withCookie( $cookie );
        }

        return $response;
    }
}

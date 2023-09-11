<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseMiddleware {
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

        if ( $response instanceof Response ) {
            return $response;
        }

        if ( $response instanceof Collection ) {
            $response = $response->toArray();
        } else if ( is_object( $response ) ) {
            $response = (array)$response;
        } else if ( empty( $response ) ) {
            $response = [];
        } else {
            return $response;
        }

        $response['status'] = 'ok';

        return response()->json( $response );
    }
}

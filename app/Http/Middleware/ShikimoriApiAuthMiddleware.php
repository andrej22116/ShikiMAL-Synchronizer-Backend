<?php

namespace App\Http\Middleware;

use App\Models\Shikimori\AuthModel;
use Closure;
use Illuminate\Http\Request;

class ShikimoriApiAuthMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle( Request $request, Closure $next ) {
        $authModel = app( AuthModel::class );

        if ( !$authModel->isAuthorized() && !$authModel->authorize() ) {
            return response()->json( [
                'state' => 'error',
                'message' => 'Not authorized!'
            ], 401 );
        }

        return $next( $request );
    }
}

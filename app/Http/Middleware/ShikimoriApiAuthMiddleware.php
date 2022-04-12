<?php

namespace App\Http\Middleware;

use App\Models\Shikimori\AuthModel;
use Closure;

class ShikimoriApiAuthMiddleware
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
        $authModel = AuthModel::instance();

        if ( !$authModel->isAuthorized() && !$authModel->authorize() ) {
            return response()->json([
                'state' => 'error',
                'message' => 'Not authorized!'
            ], 401);
        }

        return $next($request);
    }
}

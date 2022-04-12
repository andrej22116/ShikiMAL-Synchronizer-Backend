<?php

namespace App\Http\Middleware\Request;

use Closure;

class PutRequestMiddleware extends AbstractRequestMiddleware
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
        $request->request->add([
            'put' => collect( $this->rawDataToArray( $request->header('Content-Type', '') ) )
        ]);

        return $next($request);
    }
}

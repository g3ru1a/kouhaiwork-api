<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Rank2Check
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
        if($request->user()->rank < 2) {
            return response('Unauthorized.', 401);
        }

        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}

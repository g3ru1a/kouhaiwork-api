<?php

namespace App\Http\Middleware;

use Closure;

class Rank3Check
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
        if ($request->user()->rank < 3) {
            return response('Unauthorized.', 401);
        }

        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}

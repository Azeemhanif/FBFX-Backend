<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = [
            'success' => false,
            'message' => 'Unauthorized user',
            'data' => []
        ];
        if (auth()->user()->role == 'customer') {
            return $next($request);
        }
        abort(response()->json($response, 401));
    }
}

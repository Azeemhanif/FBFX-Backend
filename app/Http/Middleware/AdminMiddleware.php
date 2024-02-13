<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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

        if (auth()->user()->role == 'admin' || auth()->user()->role == 'superadmin') {
            return $next($request);
        }
        abort(response()->json($response, 401));
    }
}

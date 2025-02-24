<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {

        // If the request is to the health endpoint or home page, let it pass

        if ($request->path() === 'up' || $request->path() === '/') {
            return $next($request);
        }

        // check if request has bearer token
        if (!$request->bearerToken()) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized",
            ], 401);
        }

        if ($request->bearerToken() !== env('API_TOKEN')) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized",
            ], 401);
        }

        return $next($request);
    }
}

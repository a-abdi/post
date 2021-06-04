<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuthToken;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $access_token = $request->bearerToken();

        if( ! AuthToken::where('access_token', $access_token)->first()) {
            return response()->json([
                "message" => "You are not logged in. Please login first"
            ], 401);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRefreshToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $refreshAbility = config('auth_tokens.abilities.refresh', 'refresh');

        if (! $user || ! $user->currentAccessToken() || ! $user->tokenCan($refreshAbility)) {
            return response()->json(['message' => 'Valid refresh token required.'], 401);
        }

        return $next($request);
    }
}

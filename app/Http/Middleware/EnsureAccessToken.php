<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $accessAbility = config('auth_tokens.abilities.access', 'access');

            if (! $user->tokenCan($accessAbility) && ! $user->tokenCan('*')) {
                return response()->json([
                    'message' => 'Access token required. Use the refresh endpoint to obtain a new token.',
                ], 401);
            }
        }

        return $next($request);
    }
}

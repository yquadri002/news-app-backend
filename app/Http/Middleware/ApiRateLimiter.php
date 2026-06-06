<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        $limiter = $limiter === 'api' ? $this->resolveLimiter($request) : $limiter;
        $key = $this->resolveKey($request, $limiter);
        $maxAttempts = $this->getMaxAttempts($limiter);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests. Please slow down.',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);
        $remaining = max(0, $maxAttempts - RateLimiter::attempts($key));

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }

    private function resolveKey(Request $request, string $limiter): string
    {
        $identifier = $request->user()?->id ?? $request->ip();

        return "{$limiter}:{$identifier}";
    }

    private function getMaxAttempts(string $limiter): int
    {
        return match ($limiter) {
            'auth' => config('infrastructure.rate_limiting.auth_per_minute', 10),
            'public' => config('infrastructure.rate_limiting.public_per_minute', 300),
            default => config('infrastructure.rate_limiting.api_per_minute', 120),
        };
    }

    private function resolveLimiter(Request $request): string
    {
        if ($request->is(
            'api/v1/admin/auth/login',
            'api/v1/admin/auth/forgot-password',
            'api/v1/admin/auth/reset-password',
            'api/v1/client/device/register',
            'api/v1/client/auth/refresh',
            'api/v1/admin/auth/refresh',
        )) {
            return 'auth';
        }

        if ($request->is('api/v1/news/*', 'api/v1/client/categories', 'api/v1/client/app/*')) {
            return 'public';
        }

        return 'api';
    }
}

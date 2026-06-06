<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureMonitoringAccess
{
    public function handle(Request $request, Closure $next, string $tool = 'horizon'): Response
    {
        if (! config("infrastructure.monitoring.{$tool}_enabled", false)) {
            abort(404);
        }

        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        $admin = $this->resolveAdmin($request);

        if (! $admin || ! $admin->is_active) {
            abort(403, 'Monitoring access denied.');
        }

        $allowed = array_filter(explode(',', (string) config('infrastructure.monitoring.allowed_emails', '')));

        if (! empty($allowed) && ! in_array($admin->email, $allowed, true)) {
            abort(403, 'Monitoring access denied.');
        }

        return $next($request);
    }

    private function resolveAdmin(Request $request): ?Admin
    {
        $user = $request->user();
        if ($user instanceof Admin) {
            return $user;
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || ! $accessToken->tokenable instanceof Admin) {
            return null;
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        return $accessToken->tokenable;
    }
}

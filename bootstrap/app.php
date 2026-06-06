<?php

use App\Http\Middleware\ApiRateLimiter;
use App\Http\Middleware\CheckAdminPermission;
use App\Http\Middleware\EnsureAccessToken;
use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\EnsureMonitoringAccess;
use App\Http\Middleware\EnsureRefreshToken;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackUserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.active' => EnsureAdminIsActive::class,
            'admin.permission' => CheckAdminPermission::class,
            'track.activity' => TrackUserActivity::class,
            'rate.limit' => ApiRateLimiter::class,
            'access.token' => EnsureAccessToken::class,
            'refresh.token' => EnsureRefreshToken::class,
            'monitoring.access' => EnsureMonitoringAccess::class,
        ]);

        $trustedProxies = env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies(
            at: $trustedProxies === '*' ? '*' : array_filter(explode(',', $trustedProxies)),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );
        $middleware->append(SecurityHeaders::class);
        $middleware->api(prepend: [ApiRateLimiter::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

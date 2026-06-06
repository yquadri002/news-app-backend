<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Article;
use App\Models\Category;
use App\Models\Role;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\RolePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Telescope;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->enforceProductionSafety();

        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Gate::define('viewPulse', function (?Admin $user = null) {
            if (! config('infrastructure.monitoring.pulse_enabled', false)) {
                return false;
            }

            $allowed = array_filter(explode(',', (string) config('infrastructure.monitoring.allowed_emails', '')));

            return $user && (empty($allowed) || in_array($user->email, $allowed, true));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }

    private function enforceProductionSafety(): void
    {
        if (! $this->app->environment('production') || $this->app->runningUnitTests()) {
            return;
        }

        if (config('app.debug')) {
            throw new \RuntimeException('APP_DEBUG must be false in production.');
        }

        if (config('infrastructure.monitoring.telescope_enabled', false)) {
            Telescope::stopRecording();
        }
    }
}

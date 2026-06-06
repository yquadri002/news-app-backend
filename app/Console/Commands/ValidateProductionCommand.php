<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateProductionCommand extends Command
{
    protected $signature = 'infrastructure:validate-production';

    protected $description = 'Validate production safety configuration before deployment';

    public function handle(): int
    {
        $errors = [];

        if (config('app.env') !== 'production') {
            $this->warn('APP_ENV is not production; running checks anyway.');
        }

        if (config('app.debug')) {
            $errors[] = 'APP_DEBUG must be false in production.';
        }

        if (empty(config('app.key'))) {
            $errors[] = 'APP_KEY must be set.';
        }

        if (config('infrastructure.monitoring.telescope_enabled')) {
            $errors[] = 'TELESCOPE_ENABLED must be false in production.';
        }

        if (config('infrastructure.monitoring.pulse_enabled')) {
            $errors[] = 'PULSE_ENABLED must be false in production.';
        }

        if (empty(config('cors.allowed_origins'))) {
            $errors[] = 'CORS_ALLOWED_ORIGINS must be configured for production.';
        }

        if (config('revenue.subscription.strict_receipt_validation')
            && empty(config('revenue.subscription.apple_shared_secret'))
            && empty(config('revenue.subscription.google_service_account'))) {
            $errors[] = 'Subscription strict receipt validation is enabled but no store credentials are configured.';
        }

        foreach ($errors as $error) {
            $this->error($error);
        }

        if ($errors !== []) {
            return self::FAILURE;
        }

        $this->info('Production configuration validation passed.');

        return self::SUCCESS;
    }
}

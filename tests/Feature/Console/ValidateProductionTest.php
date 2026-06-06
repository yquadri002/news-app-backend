<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ValidateProductionTest extends TestCase
{
    public function test_production_validation_fails_when_debug_enabled(): void
    {
        Config::set('app.debug', true);
        Config::set('cors.allowed_origins', ['https://app.newshub.pro']);

        $this->assertSame(1, Artisan::call('infrastructure:validate-production'));
    }

    public function test_production_validation_passes_with_safe_configuration(): void
    {
        Config::set('app.debug', false);
        Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        Config::set('infrastructure.monitoring.telescope_enabled', false);
        Config::set('infrastructure.monitoring.pulse_enabled', false);
        Config::set('cors.allowed_origins', ['https://app.newshub.pro']);
        Config::set('revenue.subscription.strict_receipt_validation', false);

        $this->assertSame(0, Artisan::call('infrastructure:validate-production'));
    }
}

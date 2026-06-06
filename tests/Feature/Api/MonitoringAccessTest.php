<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MonitoringAccessTest extends TestCase
{
    public function test_horizon_is_hidden_when_disabled(): void
    {
        Config::set('infrastructure.monitoring.horizon_enabled', false);

        $this->get('/horizon')->assertNotFound();
    }

    public function test_pulse_is_hidden_when_disabled(): void
    {
        Config::set('infrastructure.monitoring.pulse_enabled', false);

        $this->get('/pulse')->assertNotFound();
    }

    public function test_telescope_is_hidden_when_disabled(): void
    {
        Config::set('infrastructure.monitoring.telescope_enabled', false);

        $this->get('/telescope')->assertNotFound();
    }
}

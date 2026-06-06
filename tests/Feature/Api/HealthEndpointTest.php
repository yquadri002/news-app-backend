<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_status_payload(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => ['app', 'database'],
            ]);
    }

    public function test_laravel_up_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}

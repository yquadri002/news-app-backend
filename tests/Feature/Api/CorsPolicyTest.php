<?php

namespace Tests\Feature\Api;

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CorsPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        HandleCors::flushState();
    }

    public function test_cors_allows_configured_origin_on_api_requests(): void
    {
        Config::set('cors.allowed_origins', ['https://app.newshub.pro']);

        $response = $this->withHeaders([
            'Origin' => 'https://app.newshub.pro',
        ])->getJson('/api/v1/news/feed');

        $response->assertOk();
        $response->assertHeader('Access-Control-Allow-Origin', 'https://app.newshub.pro');
    }

    public function test_cors_does_not_reflect_unlisted_origins(): void
    {
        Config::set('cors.allowed_origins', [
            'https://app.newshub.pro',
            'https://admin.newshub.pro',
        ]);
        HandleCors::flushState();

        $response = $this->withHeaders([
            'Origin' => 'https://evil.example',
        ])->getJson('/api/v1/news/feed');

        $response->assertOk();
        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}

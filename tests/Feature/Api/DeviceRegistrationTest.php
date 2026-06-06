<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeviceRegistrationTest extends TestCase
{
    public function test_device_can_register_and_receive_tokens(): void
    {
        $response = $this->postJson('/api/v1/client/device/register', [
            'device_id' => 'device-abc-123',
            'platform' => 'ios',
            'language' => 'en',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'token',
                    'tokens' => ['access_token', 'refresh_token'],
                    'preferences',
                ],
            ]);

        $this->assertDatabaseHas('users', ['device_id' => 'device-abc-123']);
    }

    public function test_existing_device_update_does_not_count_toward_registration_limit(): void
    {
        Config::set('infrastructure.device_registration.per_minute', 1);
        Config::set('infrastructure.device_registration.per_day', 5);

        User::factory()->create(['device_id' => 'existing-device']);

        $this->postJson('/api/v1/client/device/register', [
            'device_id' => 'existing-device',
            'platform' => 'android',
        ])->assertCreated();

        $this->postJson('/api/v1/client/device/register', [
            'device_id' => 'existing-device',
            'platform' => 'android',
        ])->assertCreated();
    }

    public function test_device_registration_is_rate_limited_per_ip(): void
    {
        Config::set('infrastructure.device_registration.per_minute', 2);
        Config::set('infrastructure.device_registration.per_day', 10);

        $this->postJson('/api/v1/client/device/register', ['device_id' => 'device-1', 'platform' => 'ios'])
            ->assertCreated();
        $this->postJson('/api/v1/client/device/register', ['device_id' => 'device-2', 'platform' => 'ios'])
            ->assertCreated();

        $this->postJson('/api/v1/client/device/register', ['device_id' => 'device-3', 'platform' => 'ios'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    public function test_client_can_refresh_with_refresh_token(): void
    {
        $user = User::factory()->create();
        $refreshToken = $user->createToken('refresh', ['refresh'], now()->addDay())->plainTextToken;

        $this->withToken($refreshToken)
            ->postJson('/api/v1/client/auth/refresh')
            ->assertOk()
            ->assertJsonStructure(['data' => ['tokens' => ['access_token', 'refresh_token']]]);
    }
}

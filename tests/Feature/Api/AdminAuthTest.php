<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    public function test_admin_login_returns_token_pair(): void
    {
        $role = Role::factory()->create(['slug' => 'super-admin']);
        Admin::factory()->create([
            'role_id' => $role->id,
            'email' => 'admin@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'device_name' => 'test-panel',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'admin' => ['id', 'email'],
                    'token',
                    'tokens' => ['access_token', 'refresh_token', 'expires_in'],
                ],
            ]);
    }

    public function test_access_token_is_required_for_protected_admin_routes(): void
    {
        $role = Role::factory()->create();
        $admin = Admin::factory()->create(['role_id' => $role->id]);
        $refreshToken = $admin->createToken('refresh', ['refresh'], now()->addDay())->plainTextToken;

        $this->withToken($refreshToken)
            ->getJson('/api/v1/admin/auth/me')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Access token required. Use the refresh endpoint to obtain a new token.']);
    }

    public function test_admin_can_refresh_tokens(): void
    {
        $role = Role::factory()->create();
        $admin = Admin::factory()->create(['role_id' => $role->id]);
        $refreshToken = $admin->createToken('refresh', ['refresh'], now()->addDay())->plainTextToken;

        $response = $this->withToken($refreshToken)
            ->postJson('/api/v1/admin/auth/refresh', ['device_name' => 'test-panel']);

        $response->assertOk()
            ->assertJsonPath('data.tokens.access_token', fn ($value) => is_string($value) && $value !== '');
    }

    public function test_admin_me_works_with_access_token(): void
    {
        $role = Role::factory()->create();
        $admin = Admin::factory()->create(['role_id' => $role->id, 'email' => 'me@example.com']);
        $accessToken = $admin->createToken('access', ['access'], now()->addHour())->plainTextToken;

        $this->withToken($accessToken)
            ->getJson('/api/v1/admin/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'me@example.com');
    }
}

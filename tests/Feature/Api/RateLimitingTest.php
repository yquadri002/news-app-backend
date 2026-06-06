<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    public function test_admin_login_is_rate_limited(): void
    {
        Config::set('infrastructure.rate_limiting.auth_per_minute', 2);
        RateLimiter::clear('auth:127.0.0.1');

        $role = Role::factory()->create();
        Admin::factory()->create([
            'role_id' => $role->id,
            'email' => 'limited@example.com',
            'password' => Hash::make('password'),
        ]);

        $payload = ['email' => 'limited@example.com', 'password' => 'wrong'];

        $this->postJson('/api/v1/admin/auth/login', $payload)->assertUnprocessable();
        $this->postJson('/api/v1/admin/auth/login', $payload)->assertUnprocessable();

        $this->postJson('/api/v1/admin/auth/login', $payload)
            ->assertStatus(429)
            ->assertJsonStructure(['message', 'retry_after']);
    }
}

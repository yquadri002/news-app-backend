<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'device_id' => 'device-'.Str::uuid(),
            'platform' => fake()->randomElement(['ios', 'android']),
            'language' => 'en',
            'last_active_at' => now(),
        ];
    }
}

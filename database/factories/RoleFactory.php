<?php

namespace Database\Factories;

use App\Enums\AdminPermission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => 'Super Admin',
            'slug' => 'super-admin-'.fake()->unique()->slug(2),
            'permissions' => AdminPermission::all(),
            'description' => 'Test role',
            'is_system' => true,
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'super-admin')->first();

        Admin::updateOrCreate(
            ['email' => 'admin@newshub.pro'],
            [
                'role_id' => $role->id,
                'name' => 'NewsHub Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
    }
}

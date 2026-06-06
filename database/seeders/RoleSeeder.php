<?php

namespace Database\Seeders;

use App\Enums\AdminPermission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'permissions' => AdminPermission::all(),
                'description' => 'Full system access',
                'is_system' => true,
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'editor'],
            [
                'name' => 'Editor',
                'permissions' => [
                    AdminPermission::DashboardView->value,
                    AdminPermission::CategoriesManage->value,
                    AdminPermission::BreakingNewsManage->value,
                    AdminPermission::NotificationsManage->value,
                ],
                'description' => 'Content and notification management',
                'is_system' => true,
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'analyst'],
            [
                'name' => 'Analyst',
                'permissions' => [
                    AdminPermission::DashboardView->value,
                    AdminPermission::AnalyticsView->value,
                ],
                'description' => 'Analytics and reporting access',
                'is_system' => true,
            ]
        );
    }
}

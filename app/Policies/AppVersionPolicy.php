<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AppVersion;

class AppVersionPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::AppUpdatesManage->value);
    }

    public function view(Admin $admin, AppVersion $appVersion): bool
    {
        return $admin->hasPermission(AdminPermission::AppUpdatesManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::AppUpdatesManage->value);
    }

    public function update(Admin $admin, AppVersion $appVersion): bool
    {
        return $admin->hasPermission(AdminPermission::AppUpdatesManage->value);
    }

    public function delete(Admin $admin, AppVersion $appVersion): bool
    {
        return $admin->hasPermission(AdminPermission::AppUpdatesManage->value);
    }
}

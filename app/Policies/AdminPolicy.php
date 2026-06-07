<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::AdminsManage->value);
    }

    public function view(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermission(AdminPermission::AdminsManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::AdminsManage->value);
    }

    public function update(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermission(AdminPermission::AdminsManage->value);
    }

    public function delete(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermission(AdminPermission::AdminsManage->value)
            && $admin->id !== $model->id;
    }
}

<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Role;

class RolePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::RolesManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::RolesManage->value);
    }

    public function update(Admin $admin, Role $role): bool
    {
        return $admin->hasPermission(AdminPermission::RolesManage->value);
    }

    public function delete(Admin $admin, Role $role): bool
    {
        return $admin->hasPermission(AdminPermission::RolesManage->value) && ! $role->is_system;
    }
}

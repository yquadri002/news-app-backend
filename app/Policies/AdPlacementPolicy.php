<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\AdPlacement;

class AdPlacementPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::AdsManage->value);
    }

    public function view(Admin $admin, AdPlacement $adPlacement): bool
    {
        return $admin->hasPermission(AdminPermission::AdsManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::AdsManage->value);
    }

    public function update(Admin $admin, AdPlacement $adPlacement): bool
    {
        return $admin->hasPermission(AdminPermission::AdsManage->value);
    }

    public function delete(Admin $admin, AdPlacement $adPlacement): bool
    {
        return $admin->hasPermission(AdminPermission::AdsManage->value);
    }
}

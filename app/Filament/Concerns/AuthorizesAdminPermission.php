<?php

namespace App\Filament\Concerns;

use App\Enums\AdminPermission;
use App\Models\Admin;

trait AuthorizesAdminPermission
{
    abstract protected static function requiredPermission(): ?AdminPermission;

    public static function canAccess(): bool
    {
        $permission = static::requiredPermission();

        if ($permission === null) {
            return true;
        }

        $admin = auth('admin')->user();

        return $admin instanceof Admin && $admin->hasPermission($permission->value);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

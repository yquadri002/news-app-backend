<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Notification;

class NotificationPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::NotificationsManage->value);
    }

    public function view(Admin $admin, Notification $notification): bool
    {
        return $admin->hasPermission(AdminPermission::NotificationsManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::NotificationsManage->value);
    }

    public function update(Admin $admin, Notification $notification): bool
    {
        return $admin->hasPermission(AdminPermission::NotificationsManage->value);
    }

    public function delete(Admin $admin, Notification $notification): bool
    {
        return $admin->hasPermission(AdminPermission::NotificationsManage->value);
    }
}

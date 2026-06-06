<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;

class RevenuePolicy
{
    public function viewDashboard(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::RevenueManage->value)
            || $admin->hasPermission(AdminPermission::AnalyticsView->value);
    }

    public function manage(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::RevenueManage->value);
    }

    public function viewAnalytics(Admin $admin): bool
    {
        return $this->viewDashboard($admin);
    }

    public function manageAbTests(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::RevenueManage->value)
            || $admin->hasPermission(AdminPermission::AdsManage->value);
    }
}

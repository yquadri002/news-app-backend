<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\RssSource;

class RssSourcePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }

    public function view(Admin $admin, RssSource $rssSource): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }

    public function update(Admin $admin, RssSource $rssSource): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }

    public function delete(Admin $admin, RssSource $rssSource): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }
}

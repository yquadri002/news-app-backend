<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Article;

class ArticlePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }

    public function moderate(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }

    public function approve(Admin $admin, Article $article): bool
    {
        return $admin->hasPermission(AdminPermission::SourcesManage->value);
    }
}

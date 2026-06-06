<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::CategoriesManage->value);
    }

    public function view(Admin $admin, Category $category): bool
    {
        return $admin->hasPermission(AdminPermission::CategoriesManage->value);
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermission(AdminPermission::CategoriesManage->value);
    }

    public function update(Admin $admin, Category $category): bool
    {
        return $admin->hasPermission(AdminPermission::CategoriesManage->value);
    }

    public function delete(Admin $admin, Category $category): bool
    {
        return $admin->hasPermission(AdminPermission::CategoriesManage->value);
    }
}

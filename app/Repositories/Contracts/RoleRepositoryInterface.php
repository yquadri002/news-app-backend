<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Role;
}

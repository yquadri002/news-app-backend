<?php

namespace App\Repositories\Contracts;

use App\Models\Admin;

interface AdminRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?Admin;

    public function updateLastLogin(Admin $admin): void;
}

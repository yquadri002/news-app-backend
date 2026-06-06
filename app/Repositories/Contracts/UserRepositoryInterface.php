<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByDeviceId(string $deviceId): ?User;

    public function getActiveUsersCount(int $days = 7): int;

    public function getUsersForNotification(array $filters): \Illuminate\Database\Eloquent\Collection;
}

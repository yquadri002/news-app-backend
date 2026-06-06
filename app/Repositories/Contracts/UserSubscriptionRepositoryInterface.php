<?php

namespace App\Repositories\Contracts;

interface UserSubscriptionRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveForUser(int $userId): ?\App\Models\UserSubscription;

    public function getSubscriptionMetrics(array $dateRange): array;
}

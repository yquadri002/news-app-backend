<?php

namespace App\Services;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class DashboardService
{
    public function __construct(
        private readonly AnalyticsRepositoryInterface $analyticsRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function getOverview(array $dateRange = []): array
    {
        $metrics = $this->analyticsRepository->getDashboardMetrics($dateRange);

        return [
            'total_users' => $metrics['total_users'],
            'active_users' => $metrics['active_users'],
            'articles_opened' => $metrics['articles_opened'],
            'notifications_sent' => $metrics['notifications_sent'],
            'revenue_overview' => $metrics['revenue_overview'],
            'source_performance' => $metrics['source_performance'],
            'active_users_7d' => $this->userRepository->getActiveUsersCount(7),
            'active_users_30d' => $this->userRepository->getActiveUsersCount(30),
        ];
    }
}

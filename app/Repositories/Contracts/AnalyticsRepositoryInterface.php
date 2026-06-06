<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    public function recordArticleView(array $data): void;

    public function recordSearch(array $data): void;

    public function recordEvent(array $data): void;

    public function getDashboardMetrics(array $dateRange): array;

    public function getCategoryAnalytics(int $categoryId, array $dateRange): array;

    public function getRetentionData(array $dateRange): array;

    public function getSearchTrends(array $dateRange, int $limit = 20): array;
}

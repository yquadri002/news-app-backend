<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface RevenueEventRepositoryInterface extends BaseRepositoryInterface
{
    public function recordEvent(array $data): void;

    public function getAggregatedMetrics(array $dateRange, array $groupBy = []): Collection;

    public function getRevenueByDimension(string $dimension, array $dateRange): array;
}

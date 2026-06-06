<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface GrowthMetricRepositoryInterface extends BaseRepositoryInterface
{
    public function upsertForDate(string $date, array $data): \App\Models\GrowthMetric;

    public function getRange(array $dateRange): Collection;
}

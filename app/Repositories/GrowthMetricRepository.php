<?php

namespace App\Repositories;

use App\Models\GrowthMetric;
use App\Repositories\Contracts\GrowthMetricRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GrowthMetricRepository extends BaseRepository implements GrowthMetricRepositoryInterface
{
    public function __construct(GrowthMetric $model)
    {
        parent::__construct($model);
    }

    public function upsertForDate(string $date, array $data): GrowthMetric
    {
        return $this->query()->updateOrCreate(['date' => $date], $data);
    }

    public function getRange(array $dateRange): Collection
    {
        return $this->query()
            ->whereBetween('date', [
                $dateRange['from'] ?? now()->subDays(30)->toDateString(),
                $dateRange['to'] ?? now()->toDateString(),
            ])
            ->orderBy('date')
            ->get();
    }
}

<?php

namespace App\Repositories;

use App\Models\AdRevenueSnapshot;
use App\Repositories\Contracts\AdRevenueSnapshotRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AdRevenueSnapshotRepository extends BaseRepository implements AdRevenueSnapshotRepositoryInterface
{
    public function __construct(AdRevenueSnapshot $model)
    {
        parent::__construct($model);
    }

    public function upsertSnapshot(array $data): void
    {
        $this->query()->updateOrCreate(
            [
                'date' => $data['date'],
                'ad_network' => $data['ad_network'] ?? null,
                'ad_placement_id' => $data['ad_placement_id'] ?? null,
                'country' => $data['country'] ?? null,
                'platform' => $data['platform'] ?? null,
                'category_id' => $data['category_id'] ?? null,
            ],
            $data
        );
    }

    public function getSnapshotsForRange(array $dateRange, array $filters = []): Collection
    {
        $query = $this->query()->whereBetween('date', [
            $dateRange['from'] ?? now()->subDays(30)->toDateString(),
            $dateRange['to'] ?? now()->toDateString(),
        ]);

        if (! empty($filters['ad_network'])) {
            $query->where('ad_network', $filters['ad_network']);
        }

        return $query->orderBy('date')->get();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['from'])) {
            $query->where('date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('date', '<=', $filters['to']);
        }

        return $query->orderByDesc('date');
    }
}

<?php

namespace App\Repositories;

use App\Models\RevenueEvent;
use App\Repositories\Contracts\RevenueEventRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RevenueEventRepository extends BaseRepository implements RevenueEventRepositoryInterface
{
    public function __construct(RevenueEvent $model)
    {
        parent::__construct($model);
    }

    public function recordEvent(array $data): void
    {
        $this->create($data);
    }

    public function getAggregatedMetrics(array $dateRange, array $groupBy = []): Collection
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        $query = $this->query()
            ->whereBetween('occurred_at', [$from, $to]);

        $select = [
            DB::raw('COUNT(CASE WHEN event_type = "impression" THEN 1 END) as impressions'),
            DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as clicks'),
            DB::raw('SUM(amount) as revenue'),
        ];

        foreach ($groupBy as $field) {
            $query->groupBy($field);
            $select[] = $field;
        }

        return $query->select($select)->get();
    }

    public function getRevenueByDimension(string $dimension, array $dateRange): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        $allowed = ['country', 'platform', 'category_id', 'ad_network'];
        if (! in_array($dimension, $allowed)) {
            return [];
        }

        return $this->query()
            ->whereBetween('occurred_at', [$from, $to])
            ->whereNotNull($dimension)
            ->select($dimension, DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as events'))
            ->groupBy($dimension)
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (! empty($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to']);
        }

        return $query->orderByDesc('occurred_at');
    }
}

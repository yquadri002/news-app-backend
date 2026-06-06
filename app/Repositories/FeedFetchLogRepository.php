<?php

namespace App\Repositories;

use App\Enums\FeedFetchStatus;
use App\Models\FeedFetchLog;
use App\Repositories\Contracts\FeedFetchLogRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FeedFetchLogRepository extends BaseRepository implements FeedFetchLogRepositoryInterface
{
    public function __construct(FeedFetchLog $model)
    {
        parent::__construct($model);
    }

    public function createLog(int $sourceId, array $data): FeedFetchLog
    {
        return $this->query()->create(array_merge($data, [
            'rss_source_id' => $sourceId,
            'status' => FeedFetchStatus::Started,
            'started_at' => now(),
        ]));
    }

    public function completeLog(FeedFetchLog $log, array $data): FeedFetchLog
    {
        $log->update(array_merge($data, ['completed_at' => now()]));

        return $log->fresh();
    }

    public function getRecentForSource(int $sourceId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query()
            ->where('rss_source_id', $sourceId)
            ->latest('started_at')
            ->limit($limit)
            ->get();
    }

    public function getDashboardStats(array $dateRange): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(7));
        $to = Carbon::parse($dateRange['to'] ?? now());

        $query = $this->query()->whereBetween('started_at', [$from, $to]);

        return [
            'total_fetches' => (clone $query)->count(),
            'successful' => (clone $query)->where('status', FeedFetchStatus::Success)->count(),
            'failed' => (clone $query)->where('status', FeedFetchStatus::Failed)->count(),
            'total_items_processed' => (clone $query)->sum('items_processed'),
            'avg_duration_ms' => (int) (clone $query)->avg('duration_ms'),
        ];
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['rss_source_id'])) {
            $query->where('rss_source_id', $filters['rss_source_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with('rssSource')->latest('started_at');
    }
}

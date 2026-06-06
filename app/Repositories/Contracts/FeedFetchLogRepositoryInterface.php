<?php

namespace App\Repositories\Contracts;

use App\Models\FeedFetchLog;

interface FeedFetchLogRepositoryInterface extends BaseRepositoryInterface
{
    public function createLog(int $sourceId, array $data): FeedFetchLog;

    public function completeLog(FeedFetchLog $log, array $data): FeedFetchLog;

    public function getRecentForSource(int $sourceId, int $limit = 20): \Illuminate\Database\Eloquent\Collection;

    public function getDashboardStats(array $dateRange): array;
}

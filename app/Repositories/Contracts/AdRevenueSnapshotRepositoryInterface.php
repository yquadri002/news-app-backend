<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AdRevenueSnapshotRepositoryInterface extends BaseRepositoryInterface
{
    public function upsertSnapshot(array $data): void;

    public function getSnapshotsForRange(array $dateRange, array $filters = []): Collection;
}

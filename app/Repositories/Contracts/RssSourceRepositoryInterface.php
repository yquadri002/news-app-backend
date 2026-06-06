<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface RssSourceRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveByPriority(): Collection;

    public function getUnhealthy(): Collection;

    public function updateHealth(int $id, array $healthData): void;

    public function getDueForFetch(): Collection;

    public function markFetched(int $id): void;
}

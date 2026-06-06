<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AdPlacementRepositoryInterface extends BaseRepositoryInterface
{
    public function getEnabledWithConfig(): Collection;

    public function findByKey(string $key): ?\App\Models\AdPlacement;
}

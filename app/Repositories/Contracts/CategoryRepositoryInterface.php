<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getEnabledOrdered(): Collection;

    public function updateSortOrder(array $orderedIds): void;
}

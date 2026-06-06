<?php

namespace App\Repositories;

use App\Models\AdPlacement;
use App\Repositories\Contracts\AdPlacementRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AdPlacementRepository extends BaseRepository implements AdPlacementRepositoryInterface
{
    public function __construct(AdPlacement $model)
    {
        parent::__construct($model);
    }

    public function getEnabledWithConfig(): Collection
    {
        return $this->query()
            ->where('is_enabled', true)
            ->with('abTests')
            ->orderBy('sort_order')
            ->get();
    }

    public function findByKey(string $key): ?AdPlacement
    {
        return $this->query()->where('placement_key', $key)->first();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (isset($filters['is_enabled'])) {
            $query->where('is_enabled', (bool) $filters['is_enabled']);
        }

        return $query->with('abTests')->orderBy('sort_order');
    }
}

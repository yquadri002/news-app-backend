<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getEnabledOrdered(): Collection
    {
        return $this->query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function updateSortOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $this->query()->where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (isset($filters['is_enabled'])) {
            $query->where('is_enabled', (bool) $filters['is_enabled']);
        }

        return $query->orderBy('sort_order');
    }
}

<?php

namespace App\Repositories;

use App\Enums\RssHealthStatus;
use App\Models\RssSource;
use App\Repositories\Contracts\RssSourceRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RssSourceRepository extends BaseRepository implements RssSourceRepositoryInterface
{
    public function __construct(RssSource $model)
    {
        parent::__construct($model);
    }

    public function getActiveByPriority(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->with('category')
            ->get();
    }

    public function getUnhealthy(): Collection
    {
        return $this->query()
            ->whereIn('health_status', [
                RssHealthStatus::Degraded->value,
                RssHealthStatus::Unhealthy->value,
            ])
            ->get();
    }

    public function updateHealth(int $id, array $healthData): void
    {
        $this->query()->where('id', $id)->update($healthData);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('url', 'like', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (! empty($filters['health_status'])) {
            $query->where('health_status', $filters['health_status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderByDesc('priority')->with('category');
    }
}

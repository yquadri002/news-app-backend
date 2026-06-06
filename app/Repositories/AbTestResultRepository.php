<?php

namespace App\Repositories;

use App\Enums\AbTestStatus;
use App\Models\AbTestResult;
use App\Repositories\Contracts\AbTestResultRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AbTestResultRepository extends BaseRepository implements AbTestResultRepositoryInterface
{
    public function __construct(AbTestResult $model)
    {
        parent::__construct($model);
    }

    public function getActiveTests(): Collection
    {
        return $this->query()
            ->where('status', AbTestStatus::Active)
            ->get();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['test_type'])) {
            $query->where('test_type', $filters['test_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('started_at');
    }
}

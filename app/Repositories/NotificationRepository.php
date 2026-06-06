<?php

namespace App\Repositories;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function getScheduledDue(): Collection
    {
        return $this->query()
            ->where('status', NotificationStatus::Scheduled)
            ->where('scheduled_at', '<=', now())
            ->get();
    }

    public function updateDeliveryStats(Notification $notification, array $stats): void
    {
        $notification->update($stats);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        return $query->with('creator')->latest();
    }
}

<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByDeviceId(string $deviceId): ?User
    {
        return $this->query()->where('device_id', $deviceId)->first();
    }

    public function getActiveUsersCount(int $days = 7): int
    {
        return $this->query()
            ->where('last_active_at', '>=', now()->subDays($days))
            ->count();
    }

    public function getUsersForNotification(array $filters): Collection
    {
        $query = $this->query()->whereNotNull('fcm_token');

        $query->where(function (Builder $q) use ($filters) {
            $q->whereDoesntHave('preferences')
                ->orWhereHas('preferences', function (Builder $prefs) use ($filters) {
                    $prefs->where('notifications_enabled', true);
                    if (! empty($filters['breaking_only'])) {
                        $prefs->where('breaking_news_enabled', true);
                    }
                });
        });

        if (! empty($filters['category_ids'])) {
            $query->whereHas('preferences', function (Builder $q) use ($filters) {
                foreach ($filters['category_ids'] as $categoryId) {
                    $q->whereJsonContains('category_ids', (int) $categoryId);
                }
            });
        }

        if (! empty($filters['segment_ids'])) {
            $query->whereHas('segmentMemberships', function (Builder $q) use ($filters) {
                $q->whereIn('user_segment_id', $filters['segment_ids']);
            });
        }

        if (! empty($filters['segment_criteria'])) {
            $criteria = $filters['segment_criteria'];
            if (! empty($criteria['language'])) {
                $query->where('language', $criteria['language']);
            }
            if (! empty($criteria['platform'])) {
                $query->where('platform', $criteria['platform']);
            }
        }

        if (! empty($filters['user_ids'])) {
            $query->whereIn('id', $filters['user_ids']);
        }

        return $query->get();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('device_id', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->with('preferences')->latest('last_active_at');
    }
}

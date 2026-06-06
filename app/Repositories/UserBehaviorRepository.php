<?php

namespace App\Repositories;

use App\Enums\BehaviorEventType;
use App\Models\UserBehaviorEvent;
use App\Repositories\Contracts\UserBehaviorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserBehaviorRepository implements UserBehaviorRepositoryInterface
{
    public function record(array $data): UserBehaviorEvent
    {
        return UserBehaviorEvent::create(array_merge($data, [
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]));
    }

    public function getRecentForUser(int $userId, int $days = 30): Collection
    {
        return UserBehaviorEvent::where('user_id', $userId)
            ->where('occurred_at', '>=', now()->subDays($days))
            ->orderByDesc('occurred_at')
            ->get();
    }

    public function getEventCountsByType(int $userId, int $days = 30): array
    {
        return UserBehaviorEvent::where('user_id', $userId)
            ->where('occurred_at', '>=', now()->subDays($days))
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();
    }

    public function getReadArticleIds(int $userId, int $days = 30): array
    {
        return UserBehaviorEvent::where('user_id', $userId)
            ->whereIn('event_type', [
                BehaviorEventType::ArticleOpen->value,
                BehaviorEventType::ReadTime->value,
            ])
            ->where('occurred_at', '>=', now()->subDays($days))
            ->whereNotNull('article_id')
            ->pluck('article_id')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getSessionDuration(int $userId, string $sessionId): int
    {
        $start = UserBehaviorEvent::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('event_type', BehaviorEventType::SessionStart)
            ->value('occurred_at');

        $end = UserBehaviorEvent::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('event_type', BehaviorEventType::SessionEnd)
            ->value('occurred_at');

        if (! $start) {
            return 0;
        }

        return (int) ($end ?? now())->diffInSeconds($start);
    }
}

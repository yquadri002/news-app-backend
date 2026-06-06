<?php

namespace App\Repositories\Contracts;

use App\Models\UserBehaviorEvent;
use Illuminate\Database\Eloquent\Collection;

interface UserBehaviorRepositoryInterface
{
    public function record(array $data): UserBehaviorEvent;

    public function getRecentForUser(int $userId, int $days = 30): Collection;

    public function getEventCountsByType(int $userId, int $days = 30): array;

    public function getReadArticleIds(int $userId, int $days = 30): array;

    public function getSessionDuration(int $userId, string $sessionId): int;
}

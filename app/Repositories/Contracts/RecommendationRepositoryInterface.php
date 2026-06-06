<?php

namespace App\Repositories\Contracts;

use App\Enums\RecommendationFeedType;
use App\Models\RecommendationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface RecommendationRepositoryInterface
{
    public function getCandidateArticles(User $user, int $limit = 200): Collection;

    public function logRecommendations(int $userId, RecommendationFeedType $feedType, SupportCollection $scoredArticles, ?string $sessionId = null): void;

    public function recordFeedback(int $logId, array $data): RecommendationLog;

    public function getCategoryScores(int $userId): Collection;

    public function getSourceScores(int $userId): Collection;

    public function getTopicScores(int $userId): Collection;

    public function upsertCategoryScore(int $userId, int $categoryId, array $data): void;

    public function upsertSourceScore(int $userId, int $sourceId, array $data): void;

    public function upsertTopicScore(int $userId, string $topic, array $data): void;

    public function getOrCreateInterestProfile(int $userId): \App\Models\UserInterestProfile;

    public function updateInterestProfile(int $userId, array $data): void;

    public function getAnalyticsSnapshot(string $date, ?string $feedType = null): ?\App\Models\RecommendationAnalyticsSnapshot;
}

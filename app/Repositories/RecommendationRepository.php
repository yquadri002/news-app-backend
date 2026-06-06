<?php

namespace App\Repositories;

use App\Enums\ArticleStatus;
use App\Enums\ModerationStatus;
use App\Enums\RecommendationFeedType;
use App\Models\Article;
use App\Models\RecommendationAnalyticsSnapshot;
use App\Models\RecommendationLog;
use App\Models\User;
use App\Models\UserCategoryScore;
use App\Models\UserInterestProfile;
use App\Models\UserSourceScore;
use App\Models\UserTopicScore;
use App\Repositories\Contracts\RecommendationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class RecommendationRepository implements RecommendationRepositoryInterface
{
    public function getCandidateArticles(User $user, int $limit = 200): Collection
    {
        return Article::query()
            ->published()
            ->where('published_at', '>=', now()->subDays(14))
            ->with(['category', 'autoCategory', 'rssSource', 'metrics', 'tags', 'assignedCategories'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function logRecommendations(int $userId, RecommendationFeedType $feedType, SupportCollection $scoredArticles, ?string $sessionId = null): void
    {
        $now = now();
        $position = 0;

        foreach ($scoredArticles as $item) {
            RecommendationLog::create([
                'user_id' => $userId,
                'feed_type' => $feedType,
                'article_id' => $item['article']->id,
                'rank_score' => $item['score'],
                'position' => $position++,
                'session_id' => $sessionId,
                'score_breakdown' => $item['breakdown'] ?? null,
                'served_at' => $now,
            ]);
        }
    }

    public function recordFeedback(int $logId, array $data): RecommendationLog
    {
        $log = RecommendationLog::findOrFail($logId);
        $log->update($data);

        return $log->fresh();
    }

    public function getCategoryScores(int $userId): Collection
    {
        return UserCategoryScore::where('user_id', $userId)
            ->orderByDesc('score')
            ->with('category')
            ->get();
    }

    public function getSourceScores(int $userId): Collection
    {
        return UserSourceScore::where('user_id', $userId)
            ->orderByDesc('score')
            ->with('rssSource')
            ->get();
    }

    public function getTopicScores(int $userId): Collection
    {
        return UserTopicScore::where('user_id', $userId)
            ->orderByDesc('score')
            ->get();
    }

    public function upsertCategoryScore(int $userId, int $categoryId, array $data): void
    {
        UserCategoryScore::updateOrCreate(
            ['user_id' => $userId, 'category_id' => $categoryId],
            $data
        );
    }

    public function upsertSourceScore(int $userId, int $sourceId, array $data): void
    {
        UserSourceScore::updateOrCreate(
            ['user_id' => $userId, 'rss_source_id' => $sourceId],
            $data
        );
    }

    public function upsertTopicScore(int $userId, string $topic, array $data): void
    {
        UserTopicScore::updateOrCreate(
            ['user_id' => $userId, 'topic' => strtolower($topic)],
            $data
        );
    }

    public function getOrCreateInterestProfile(int $userId): UserInterestProfile
    {
        return UserInterestProfile::firstOrCreate(
            ['user_id' => $userId],
            ['is_cold_start' => true, 'profile_strength' => 0]
        );
    }

    public function updateInterestProfile(int $userId, array $data): void
    {
        UserInterestProfile::where('user_id', $userId)->update($data);
    }

    public function getAnalyticsSnapshot(string $date, ?string $feedType = null): ?RecommendationAnalyticsSnapshot
    {
        return RecommendationAnalyticsSnapshot::where('date', $date)
            ->when($feedType, fn ($q) => $q->where('feed_type', $feedType))
            ->first();
    }
}

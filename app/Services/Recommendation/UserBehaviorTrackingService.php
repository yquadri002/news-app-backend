<?php

namespace App\Services\Recommendation;

use App\Enums\BehaviorEventType;
use App\Models\User;
use App\Models\UserBookmark;
use App\Repositories\Contracts\UserBehaviorRepositoryInterface;
use App\Services\AnalyticsService;

class UserBehaviorTrackingService
{
    public function __construct(
        private readonly UserBehaviorRepositoryInterface $behaviorRepository,
        private readonly AnalyticsService $analyticsService,
    ) {
    }

    public function trackArticleOpen(User $user, int $articleId, ?string $sessionId = null, ?string $feedType = null, ?string $source = null): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::ArticleOpen,
            'article_id' => $articleId,
            'session_id' => $sessionId,
            'feed_type' => $feedType,
            'metadata' => ['source' => $source],
        ]);

        $this->analyticsService->trackArticleView([
            'article_id' => $articleId,
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'session_id' => $sessionId,
            'source' => $source ?? $feedType ?? 'recommendation',
        ]);
    }

    public function trackReadTime(User $user, int $articleId, int $seconds, ?int $scrollDepth = null, ?string $sessionId = null): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::ReadTime,
            'article_id' => $articleId,
            'read_time_seconds' => $seconds,
            'scroll_depth_percent' => $scrollDepth,
            'session_id' => $sessionId,
        ]);

        if ($scrollDepth !== null) {
            $this->behaviorRepository->record([
                'user_id' => $user->id,
                'device_id' => $user->device_id,
                'event_type' => BehaviorEventType::ScrollDepth,
                'article_id' => $articleId,
                'scroll_depth_percent' => $scrollDepth,
                'session_id' => $sessionId,
            ]);
        }
    }

    public function trackBookmark(User $user, int $articleId, bool $bookmarked = true): void
    {
        if ($bookmarked) {
            UserBookmark::firstOrCreate(['user_id' => $user->id, 'article_id' => $articleId]);
        } else {
            UserBookmark::where('user_id', $user->id)->where('article_id', $articleId)->delete();
        }

        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => $bookmarked ? BehaviorEventType::Bookmark : BehaviorEventType::Unbookmark,
            'article_id' => $articleId,
        ]);
    }

    public function trackShare(User $user, int $articleId, ?string $platform = null): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::Share,
            'article_id' => $articleId,
            'metadata' => ['platform' => $platform],
        ]);
    }

    public function trackSearch(User $user, string $query, int $resultsCount = 0): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::Search,
            'search_query' => $query,
            'metadata' => ['results_count' => $resultsCount],
        ]);

        $this->analyticsService->trackSearch([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'query' => $query,
            'results_count' => $resultsCount,
        ]);
    }

    public function trackCategoryOpen(User $user, int $categoryId, ?string $sessionId = null): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::CategoryOpen,
            'category_id' => $categoryId,
            'session_id' => $sessionId,
        ]);

        $this->analyticsService->trackCategoryView($categoryId, $user->id, $user->device_id);
    }

    public function trackSourceOpen(User $user, int $sourceId, ?string $sessionId = null): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::SourceOpen,
            'rss_source_id' => $sourceId,
            'session_id' => $sessionId,
        ]);
    }

    public function trackSessionStart(User $user, string $sessionId): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::SessionStart,
            'session_id' => $sessionId,
        ]);
    }

    public function trackSessionEnd(User $user, string $sessionId): void
    {
        $this->behaviorRepository->record([
            'user_id' => $user->id,
            'device_id' => $user->device_id,
            'event_type' => BehaviorEventType::SessionEnd,
            'session_id' => $sessionId,
        ]);
    }
}

<?php

namespace App\Services\Recommendation;

use App\Enums\RecommendationFeedType;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\RecommendationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RecommendationEngineService
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly FeedRankingService $feedRankingService,
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function getForYouFeed(User $user, int $perPage = 20, ?string $sessionId = null): array
    {
        return $this->buildFeed($user, RecommendationFeedType::ForYou, $perPage, $sessionId);
    }

    public function getFollowingFeed(User $user, int $perPage = 20, ?string $sessionId = null): array
    {
        $followedSourceIds = $user->preferences?->source_ids ?? [];
        $followedCategoryIds = $user->preferences?->category_ids ?? [];

        $candidates = $this->recommendationRepository->getCandidateArticles($user, config('recommendation.feed.max_candidates', 200))
            ->filter(function ($article) use ($followedSourceIds, $followedCategoryIds) {
                return in_array($article->rss_source_id, $followedSourceIds)
                    || in_array($article->category_id, $followedCategoryIds)
                    || in_array($article->auto_category_id, $followedCategoryIds);
            });

        return $this->buildFeedFromCandidates($user, RecommendationFeedType::Following, $candidates, $perPage, $sessionId);
    }

    public function getTrendingFeed(User $user, int $perPage = 20, ?string $sessionId = null): array
    {
        $articles = $this->articleRepository->getTrending($perPage * 2);

        return $this->buildFeedFromCandidates($user, RecommendationFeedType::Trending, $articles, $perPage, $sessionId, [
            'boost_trending' => true,
        ]);
    }

    public function getBreakingFeed(User $user, int $perPage = 20, ?string $sessionId = null): array
    {
        $articles = $this->articleRepository->getBreaking($perPage * 2);

        return $this->buildFeedFromCandidates($user, RecommendationFeedType::Breaking, $articles, $perPage, $sessionId);
    }

    public function getLocalFeed(User $user, int $perPage = 20, ?string $sessionId = null): array
    {
        $candidates = $this->recommendationRepository->getCandidateArticles($user, config('recommendation.feed.max_candidates', 200));

        return $this->buildFeedFromCandidates($user, RecommendationFeedType::Local, $candidates, $perPage, $sessionId, [
            'local_only' => true,
        ]);
    }

    public function recordFeedback(User $user, array $data): void
    {
        if (! empty($data['recommendation_log_id'])) {
            $this->recommendationRepository->recordFeedback((int) $data['recommendation_log_id'], [
                'was_clicked' => $data['was_clicked'] ?? true,
                'was_read' => $data['was_read'] ?? false,
                'read_time_seconds' => $data['read_time_seconds'] ?? null,
                'clicked_at' => now(),
            ]);
        }

        if (! empty($data['article_id'])) {
            app(UserBehaviorTrackingService::class)->trackArticleOpen(
                $user,
                (int) $data['article_id'],
                $data['session_id'] ?? null,
                $data['feed_type'] ?? 'for_you',
                'recommendation_feedback',
            );
        }

        if (! empty($data['read_time_seconds'])) {
            app(UserBehaviorTrackingService::class)->trackReadTime(
                $user,
                (int) $data['article_id'],
                (int) $data['read_time_seconds'],
                $data['scroll_depth_percent'] ?? null,
                $data['session_id'] ?? null,
            );
        }

        if (isset($data['bookmarked'])) {
            app(UserBehaviorTrackingService::class)->trackBookmark(
                $user,
                (int) $data['article_id'],
                (bool) $data['bookmarked'],
            );
        }

        if (! empty($data['shared'])) {
            app(UserBehaviorTrackingService::class)->trackShare(
                $user,
                (int) $data['article_id'],
                $data['share_platform'] ?? null,
            );
        }
    }

    private function buildFeed(User $user, RecommendationFeedType $feedType, int $perPage, ?string $sessionId): array
    {
        $candidates = $this->recommendationRepository->getCandidateArticles(
            $user,
            config('recommendation.feed.max_candidates', 200)
        );

        return $this->buildFeedFromCandidates($user, $feedType, $candidates, $perPage, $sessionId);
    }

    private function buildFeedFromCandidates(
        User $user,
        RecommendationFeedType $feedType,
        Collection $candidates,
        int $perPage,
        ?string $sessionId,
        array $options = [],
    ): array {
        $ranked = $this->feedRankingService->rankArticles($user, $candidates, $options);
        $diverse = $this->feedRankingService->applyDiversity($ranked, $perPage);

        if ($sessionId) {
            $this->recommendationRepository->logRecommendations($user->id, $feedType, $diverse, $sessionId);
        }

        $profile = $this->recommendationRepository->getOrCreateInterestProfile($user->id);

        return [
            'articles' => $diverse->pluck('article'),
            'scores' => $diverse->mapWithKeys(fn ($item) => [$item['article']->id => $item['score']]),
            'meta' => [
                'feed_type' => $feedType->value,
                'session_id' => $sessionId ?? Str::uuid()->toString(),
                'is_cold_start' => $profile->is_cold_start,
                'profile_strength' => (float) $profile->profile_strength,
                'primary_segment' => $profile->primary_segment,
                'count' => $diverse->count(),
            ],
        ];
    }
}

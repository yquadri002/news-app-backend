<?php

namespace App\Services\Recommendation;

use App\Models\Article;
use App\Models\User;
use App\Repositories\Contracts\RecommendationRepositoryInterface;
use App\Repositories\Contracts\UserBehaviorRepositoryInterface;
use Illuminate\Support\Collection;

class FeedRankingService
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly UserBehaviorRepositoryInterface $behaviorRepository,
        private readonly ColdStartStrategyService $coldStartStrategy,
    ) {
    }

    public function rankArticles(User $user, Collection $articles, array $options = []): Collection
    {
        $profile = $this->recommendationRepository->getOrCreateInterestProfile($user->id);
        $weights = config('recommendation.ranking_weights');

        $categoryScores = $this->recommendationRepository->getCategoryScores($user->id)->keyBy('category_id');
        $sourceScores = $this->recommendationRepository->getSourceScores($user->id)->keyBy('rss_source_id');
        $topicScores = $this->recommendationRepository->getTopicScores($user->id)->keyBy('topic');
        $readArticleIds = $this->behaviorRepository->getReadArticleIds($user->id, 7);

        if ($profile->is_cold_start) {
            $categoryScores = $this->coldStartStrategy->getCategoryScores($user);
            $topicScores = $this->coldStartStrategy->getTopicScores($user);
        }

        $scored = $articles->map(function (Article $article) use (
            $user, $weights, $categoryScores, $sourceScores, $topicScores, $readArticleIds, $options
        ) {
            $breakdown = [
                'user_interest' => $this->scoreUserInterest($article, $categoryScores, $sourceScores, $topicScores),
                'trending' => $this->normalizeScore((float) ($article->trending_score ?? 0), 50),
                'breaking' => $this->normalizeScore((float) ($article->breaking_score ?? 0), 30),
                'freshness' => $this->scoreFreshness($article),
                'engagement' => $this->normalizeScore((float) ($article->metrics?->engagement_score ?? 0), 30),
                'source_quality' => $this->scoreSourceQuality($article),
            ];

            $totalScore = 0;
            foreach ($breakdown as $factor => $value) {
                $totalScore += $value * ($weights[$factor] ?? 0);
            }

            if (in_array($article->id, $readArticleIds)) {
                $totalScore *= (1 - config('recommendation.feed.seen_article_penalty', 0.5));
            }

            if (! empty($options['boost_category_id']) && $article->category_id == $options['boost_category_id']) {
                $totalScore *= 1.3;
            }

            if (! empty($options['local_only']) && ! $this->isLocalArticle($article, $user)) {
                $totalScore *= 0.1;
            }

            return [
                'article' => $article,
                'score' => round($totalScore, 4),
                'breakdown' => $breakdown,
            ];
        });

        return $scored->sortByDesc('score')->values();
    }

    public function applyDiversity(Collection $scoredArticles, int $limit): Collection
    {
        $selected = collect();
        $categoryCounts = [];

        foreach ($scoredArticles as $item) {
            if ($selected->count() >= $limit) {
                break;
            }

            $categoryId = $item['article']->category_id ?? 0;
            $count = $categoryCounts[$categoryId] ?? 0;

            if ($count >= 3 && $selected->count() > 5) {
                $item['score'] *= (1 - config('recommendation.feed.diversity_penalty', 0.15));
            }

            $selected->push($item);
            $categoryCounts[$categoryId] = $count + 1;
        }

        return $selected->sortByDesc('score')->values();
    }

    private function scoreUserInterest(Article $article, $categoryScores, $sourceScores, $topicScores): float
    {
        $score = 0;

        $catId = $article->category_id ?? $article->auto_category_id;
        if ($catId && isset($categoryScores[$catId])) {
            $score += $this->normalizeScore((float) $categoryScores[$catId]->score, 30);
        }

        if ($article->rss_source_id && isset($sourceScores[$article->rss_source_id])) {
            $score += $this->normalizeScore((float) $sourceScores[$article->rss_source_id]->score, 20);
        }

        $tagMatches = 0;
        foreach ($article->tags ?? [] as $tag) {
            if (isset($topicScores[$tag->tag])) {
                $tagMatches += (float) $topicScores[$tag->tag]->score;
            }
        }
        $score += $this->normalizeScore($tagMatches, 25);

        return min(100, $score);
    }

    private function scoreFreshness(Article $article): float
    {
        $publishedAt = $article->published_at ?? $article->created_at;
        $hoursAgo = now()->diffInMinutes($publishedAt) / 60;

        return max(0, 100 * pow(0.5, $hoursAgo / 24));
    }

    private function scoreSourceQuality(Article $article): float
    {
        $source = $article->rssSource;
        if (! $source) {
            return 50;
        }

        $healthScore = match ($source->health_status?->value ?? $source->health_status) {
            'healthy' => 100,
            'degraded' => 60,
            'unhealthy' => 20,
            default => 50,
        };

        $priorityBonus = min(20, ($source->priority ?? 0) * 2);

        return min(100, $healthScore * 0.7 + $priorityBonus);
    }

    private function isLocalArticle(Article $article, User $user): bool
    {
        $location = strtolower($user->location ?? $user->preferences?->location ?? '');
        if (empty($location)) {
            return false;
        }

        $text = strtolower($article->title.' '.($article->summary ?? ''));

        return str_contains($text, $location)
            || $article->category?->slug === 'local';
    }

    private function normalizeScore(float $value, float $max): float
    {
        return min(100, ($value / max(1, $max)) * 100);
    }
}

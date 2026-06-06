<?php

namespace App\Services\Ingestion;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;

class BreakingNewsDetectionService
{
    private const BREAKING_KEYWORDS = [
        'breaking' => 10, 'urgent' => 8, 'just in' => 8, 'developing' => 6,
        'alert' => 7, 'exclusive' => 5, 'live' => 4, 'emergency' => 9,
        'crash' => 6, 'attack' => 7, 'earthquake' => 8, 'explosion' => 8,
        'shot' => 6, 'killed' => 7, 'dead' => 5, 'resigns' => 6, 'arrested' => 5,
    ];

    private const BREAKING_THRESHOLD = 15.0;

    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly DuplicateDetectionService $duplicateDetection,
    ) {
    }

    public function detect(Article $article): float
    {
        $keywordScore = $this->calculateKeywordScore($article);
        $recencyScore = $this->calculateRecencyScore($article);
        $confirmationScore = $this->calculateMultiSourceConfirmation($article);
        $trendScore = $this->calculateTrendAcceleration($article);

        $breakingScore = ($keywordScore * 0.35)
            + ($recencyScore * 0.25)
            + ($confirmationScore * 0.25)
            + ($trendScore * 0.15);

        $wasBreaking = $article->is_breaking;

        $article->update([
            'breaking_score' => round($breakingScore, 4),
            'is_breaking' => $breakingScore >= self::BREAKING_THRESHOLD,
        ]);

        if ($article->metrics) {
            $article->metrics->update(['breaking_score' => round($breakingScore, 4)]);
        }

        if (! $wasBreaking && $breakingScore >= self::BREAKING_THRESHOLD
            && config('notification_intelligence.breaking.auto_push_enabled', true)) {
            \App\Jobs\ProcessBreakingNotificationJob::dispatch($article->id);
        }

        return $breakingScore;
    }

    public function detectAll(): int
    {
        $articles = $this->articleRepository->getRecentForDuplicateCheck(6);
        $count = 0;

        foreach ($articles as $article) {
            if ($article->is_duplicate) {
                continue;
            }
            $this->detect($article->fresh());
            $count++;
        }

        return $count;
    }

    private function calculateKeywordScore(Article $article): float
    {
        $text = strtolower($article->title.' '.($article->summary ?? ''));
        $score = 0;

        foreach (self::BREAKING_KEYWORDS as $keyword => $weight) {
            if (str_contains($text, $keyword)) {
                $score += $weight;
            }
        }

        return min(30, $score);
    }

    private function calculateRecencyScore(Article $article): float
    {
        $publishedAt = $article->published_at ?? $article->created_at;
        $minutesAgo = now()->diffInMinutes($publishedAt);

        if ($minutesAgo <= 30) {
            return 30;
        }
        if ($minutesAgo <= 60) {
            return 20;
        }
        if ($minutesAgo <= 180) {
            return 10;
        }

        return max(0, 10 - ($minutesAgo / 60));
    }

    private function calculateMultiSourceConfirmation(Article $article): float
    {
        $similar = $this->articleRepository->getRecentForDuplicateCheck(12)
            ->filter(fn (Article $a) => $a->id !== $article->id
                && $this->duplicateDetection->calculateTitleSimilarity($article->title, $a->title) >= 70
                && $a->rss_source_id !== $article->rss_source_id);

        $sourceCount = $similar->pluck('rss_source_id')->unique()->count() + 1;

        return min(30, $sourceCount * 8);
    }

    private function calculateTrendAcceleration(Article $article): float
    {
        $views1h = $article->metrics?->views_1h ?? 0;
        $views24h = $article->metrics?->views_24h ?? 1;

        $velocity = $views1h / max(1, $views24h / 24);

        return min(20, $velocity * 5);
    }
}

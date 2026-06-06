<?php

namespace App\Services\Ingestion;

use App\Models\Article;
use App\Models\ArticleMetric;
use Illuminate\Support\Facades\DB;

class TrendingEngineService
{
    private const TIME_DECAY_HALF_LIFE_HOURS = 12;

    public function calculateAll(): int
    {
        $articles = Article::query()
            ->where('is_duplicate', false)
            ->where('published_at', '>=', now()->subDays(7))
            ->with('metrics')
            ->get();

        foreach ($articles as $article) {
            $this->calculateForArticle($article);
        }

        return $articles->count();
    }

    public function calculateForArticle(Article $article): float
    {
        $metrics = $article->metrics ?? ArticleMetric::create(['article_id' => $article->id]);

        $readWeight = $this->calculateReadWeight($metrics, $article);
        $velocityScore = $this->calculateVelocity($metrics);
        $engagementScore = $this->calculateEngagement($metrics);
        $recencyScore = $this->calculateTimeDecay($article);
        $confirmationBonus = min(5, ($metrics->source_confirmation_count - 1) * 1.5);

        $trendingScore = ($velocityScore * 0.40)
            + ($readWeight * 0.25)
            + ($engagementScore * 0.15)
            + ($recencyScore * 0.15)
            + ($confirmationBonus * 0.05);

        $metrics->update([
            'trending_score' => round($trendingScore, 4),
            'velocity_score' => round($velocityScore, 4),
            'engagement_score' => round($engagementScore, 4),
            'recency_score' => round($recencyScore, 4),
            'read_count' => $article->view_count,
            'last_calculated_at' => now(),
        ]);

        $article->update(['trending_score' => round($trendingScore, 4)]);

        return $trendingScore;
    }

    public function updateViewMetrics(): void
    {
        DB::table('article_metrics')
            ->join('articles', 'articles.id', '=', 'article_metrics.article_id')
            ->update(['article_metrics.read_count' => DB::raw('articles.view_count')]);

        $oneHourAgo = now()->subHour();
        $oneDayAgo = now()->subDay();

        $articles = Article::query()
            ->where('published_at', '>=', $oneDayAgo)
            ->pluck('id');

        foreach ($articles as $articleId) {
            $views1h = DB::table('article_views')
                ->where('article_id', $articleId)
                ->where('viewed_at', '>=', $oneHourAgo)
                ->count();

            $views24h = DB::table('article_views')
                ->where('article_id', $articleId)
                ->where('viewed_at', '>=', $oneDayAgo)
                ->count();

            ArticleMetric::where('article_id', $articleId)->update([
                'views_1h' => $views1h,
                'views_24h' => $views24h,
            ]);
        }
    }

    private function calculateReadWeight(ArticleMetric $metrics, Article $article): float
    {
        $reads = $article->view_count;

        return min(50, log10(max(1, $reads)) * 15);
    }

    private function calculateVelocity(ArticleMetric $metrics): float
    {
        $views1h = $metrics->views_1h;
        $views24h = max(1, $metrics->views_24h);

        $hourlyRate = $views1h;
        $avgHourlyRate = $views24h / 24;

        if ($avgHourlyRate <= 0) {
            return min(50, $hourlyRate * 2);
        }

        $acceleration = $hourlyRate / $avgHourlyRate;

        return min(50, $acceleration * 10);
    }

    private function calculateEngagement(ArticleMetric $metrics): float
    {
        $reads = max(1, $metrics->read_count);
        $shares = $metrics->share_count;

        return min(30, ($shares / $reads) * 100);
    }

    private function calculateTimeDecay(Article $article): float
    {
        $publishedAt = $article->published_at ?? $article->created_at;
        $hoursAgo = now()->diffInMinutes($publishedAt) / 60;

        return 30 * pow(0.5, $hoursAgo / self::TIME_DECAY_HALF_LIFE_HOURS);
    }
}

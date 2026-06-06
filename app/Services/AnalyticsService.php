<?php

namespace App\Services;

use App\Enums\AnalyticsEventType;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\ArticleRepositoryInterface;

class AnalyticsService
{
    public function __construct(
        private readonly AnalyticsRepositoryInterface $analyticsRepository,
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function trackArticleView(array $data): void
    {
        $this->analyticsRepository->recordArticleView([
            'article_id' => $data['article_id'],
            'user_id' => $data['user_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'session_id' => $data['session_id'] ?? null,
            'source' => $data['source'] ?? null,
            'viewed_at' => now(),
        ]);

        $this->articleRepository->incrementViewCount($data['article_id']);

        $this->analyticsRepository->recordEvent([
            'event_type' => AnalyticsEventType::ArticleOpen,
            'user_id' => $data['user_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'metadata' => ['article_id' => $data['article_id']],
            'occurred_at' => now(),
        ]);
    }

    public function trackSearch(array $data): void
    {
        $this->analyticsRepository->recordSearch([
            'user_id' => $data['user_id'] ?? null,
            'query' => $data['query'],
            'results_count' => $data['results_count'] ?? 0,
            'device_id' => $data['device_id'] ?? null,
        ]);

        $this->analyticsRepository->recordEvent([
            'event_type' => AnalyticsEventType::Search,
            'user_id' => $data['user_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'metadata' => ['query' => $data['query']],
            'occurred_at' => now(),
        ]);
    }

    public function trackCategoryView(int $categoryId, ?int $userId = null, ?string $deviceId = null): void
    {
        $this->analyticsRepository->recordEvent([
            'event_type' => AnalyticsEventType::CategoryView,
            'user_id' => $userId,
            'device_id' => $deviceId,
            'metadata' => ['category_id' => $categoryId],
            'occurred_at' => now(),
        ]);
    }

    public function getCategoryAnalytics(int $categoryId, array $dateRange): array
    {
        return $this->analyticsRepository->getCategoryAnalytics($categoryId, $dateRange);
    }

    public function getRetentionData(array $dateRange): array
    {
        return $this->analyticsRepository->getRetentionData($dateRange);
    }

    public function getSearchTrends(array $dateRange, int $limit = 20): array
    {
        return $this->analyticsRepository->getSearchTrends($dateRange, $limit);
    }
}

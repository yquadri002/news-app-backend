<?php

namespace App\Services\Revenue;

use App\Repositories\Contracts\UserSubscriptionRepositoryInterface;

class RevenueDashboardService
{
    public function __construct(
        private readonly RevenueAnalyticsService $analyticsService,
        private readonly GrowthAnalyticsService $growthService,
        private readonly SubscriptionService $subscriptionService,
        private readonly UserMonetizationSegmentationService $segmentationService,
        private readonly AdOptimizationService $optimizationService,
    ) {
    }

    public function getDashboard(array $dateRange = []): array
    {
        $analytics = $this->analyticsService->getAnalytics($dateRange);
        $growth = $this->growthService->getGrowthMetrics($dateRange);
        $subscriptions = $this->subscriptionService->getMetrics($dateRange);

        return [
            'revenue' => [
                'total' => $analytics['summary']['total_revenue'],
                'arpu' => $analytics['summary']['arpu'],
                'arpdau' => $analytics['summary']['arpdau'],
            ],
            'ads' => [
                'impressions' => $analytics['summary']['impressions'],
                'clicks' => $analytics['summary']['clicks'],
                'ctr' => $analytics['summary']['ctr'],
                'ecpm' => $analytics['summary']['ecpm'],
                'fill_rate' => $analytics['summary']['fill_rate'],
            ],
            'subscriptions' => [
                'active_subscribers' => $subscriptions['active_subscribers'],
                'mrr' => $subscriptions['mrr'],
                'trial_conversion_rate' => $subscriptions['trial_conversion_rate'],
                'churn_rate' => $subscriptions['churn_rate'],
                'subscriber_retention_30d' => $subscriptions['subscriber_retention_30d'],
            ],
            'growth' => $growth['current'],
            'ltv' => [
                'average' => $growth['current']['avg_ltv'],
            ],
            'retention' => [
                'd1' => $growth['current']['retention_d1'],
                'd7' => $growth['current']['retention_d7'],
                'd30' => $growth['current']['retention_d30'],
            ],
            'segments' => $this->segmentationService->getSegmentDistribution(),
            'top_networks' => array_slice($analytics['by_network'], 0, 5),
            'top_countries' => array_slice($analytics['by_country'], 0, 5),
            'optimization_summary' => [
                'best_placement' => $this->optimizationService->getOptimizationRecommendations()['best_placements'][0] ?? null,
                'best_network' => $this->optimizationService->getOptimizationRecommendations()['best_networks'][0] ?? null,
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}

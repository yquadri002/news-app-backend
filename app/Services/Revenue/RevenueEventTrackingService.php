<?php

namespace App\Services\Revenue;

use App\Enums\RevenueEventType;
use App\Repositories\Contracts\RevenueEventRepositoryInterface;

class RevenueEventTrackingService
{
    public function __construct(
        private readonly RevenueEventRepositoryInterface $revenueEventRepository,
    ) {
    }

    public function trackImpression(array $data): void
    {
        $this->revenueEventRepository->recordEvent([
            'user_id' => $data['user_id'] ?? null,
            'event_type' => RevenueEventType::Impression,
            'ad_network' => $data['ad_network'] ?? null,
            'ad_placement_id' => $data['ad_placement_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'] ?? $this->estimateImpressionRevenue($data),
            'currency' => $data['currency'] ?? 'USD',
            'country' => $data['country'] ?? null,
            'platform' => $data['platform'] ?? null,
            'ad_format' => $data['ad_format'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'occurred_at' => now(),
        ]);
    }

    public function trackClick(array $data): void
    {
        $this->revenueEventRepository->recordEvent([
            'user_id' => $data['user_id'] ?? null,
            'event_type' => RevenueEventType::Click,
            'ad_network' => $data['ad_network'] ?? null,
            'ad_placement_id' => $data['ad_placement_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
            'country' => $data['country'] ?? null,
            'platform' => $data['platform'] ?? null,
            'ad_format' => $data['ad_format'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'occurred_at' => now(),
        ]);
    }

    private function estimateImpressionRevenue(array $data): float
    {
        if (! empty($data['ad_network'])) {
            $network = \App\Enums\AdNetwork::tryFrom($data['ad_network']);

            return $network ? round($network->defaultEcpm() / 1000, 6) : 0.001;
        }

        return 0.001;
    }
}

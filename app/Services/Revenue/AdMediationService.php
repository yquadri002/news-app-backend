<?php

namespace App\Services\Revenue;

use App\Enums\AdNetwork;
use App\Models\AdMediationWaterfall;
use App\Models\AdPlacement;
use App\Models\AdRevenueSnapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdMediationService
{
    public function initializeWaterfall(AdPlacement $placement): void
    {
        $priority = 1;
        foreach (AdNetwork::cases() as $network) {
            AdMediationWaterfall::updateOrCreate(
                ['ad_placement_id' => $placement->id, 'ad_network' => $network],
                [
                    'priority' => $priority++,
                    'floor_price' => $network->defaultEcpm() * 0.5,
                    'historical_ecpm' => $network->defaultEcpm(),
                    'fill_rate' => 0.8,
                    'is_enabled' => true,
                ]
            );
        }
    }

    public function optimizeWaterfall(?int $placementId = null): int
    {
        $placements = $placementId
            ? AdPlacement::where('id', $placementId)->get()
            : AdPlacement::where('is_enabled', true)->get();

        $optimized = 0;

        foreach ($placements as $placement) {
            if ($placement->mediationWaterfall->isEmpty()) {
                $this->initializeWaterfall($placement);
            }

            $networkStats = $this->getNetworkPerformance($placement->id);

            foreach ($placement->mediationWaterfall as $entry) {
                $stats = $networkStats->get($entry->ad_network->value);
                if (! $stats) {
                    continue;
                }

                $score = $this->calculateNetworkScore($stats);
                $entry->update([
                    'historical_ecpm' => $stats['ecpm'],
                    'fill_rate' => $stats['fill_rate'],
                    'priority' => (int) round((1 - $score) * 100),
                ]);
                $optimized++;
            }

            $this->reorderWaterfall($placement);
        }

        return $optimized;
    }

    public function getWaterfallForPlacement(AdPlacement $placement): Collection
    {
        if ($placement->mediationWaterfall->isEmpty()) {
            $this->initializeWaterfall($placement);
            $placement->load('mediationWaterfall');
        }

        return $placement->mediationWaterfall
            ->where('is_enabled', true)
            ->sortBy('priority')
            ->values()
            ->map(fn (AdMediationWaterfall $entry) => [
                'network' => $entry->ad_network->value,
                'network_label' => $entry->ad_network->label(),
                'priority' => $entry->priority,
                'floor_price' => (float) $entry->floor_price,
                'historical_ecpm' => (float) $entry->historical_ecpm,
                'fill_rate' => (float) $entry->fill_rate,
            ]);
    }

    private function getNetworkPerformance(int $placementId): Collection
    {
        return AdRevenueSnapshot::query()
            ->where('ad_placement_id', $placementId)
            ->where('date', '>=', now()->subDays(14))
            ->whereNotNull('ad_network')
            ->select('ad_network', DB::raw('AVG(ecpm) as ecpm'), DB::raw('AVG(fill_rate) as fill_rate'), DB::raw('AVG(ctr) as ctr'))
            ->groupBy('ad_network')
            ->get()
            ->keyBy(fn ($row) => $row->ad_network?->value ?? $row->ad_network)
            ->map(fn ($row) => [
                'ecpm' => (float) $row->ecpm,
                'fill_rate' => (float) $row->fill_rate,
                'ctr' => (float) $row->ctr,
            ]);
    }

    private function calculateNetworkScore(array $stats): float
    {
        $ecpmWeight = config('revenue.optimization.ecpm_weight', 0.4);
        $fillWeight = config('revenue.optimization.fill_rate_weight', 0.3);
        $ctrWeight = config('revenue.optimization.ctr_weight', 0.3);

        $ecpmNorm = min(1, $stats['ecpm'] / 5.0);
        $fillNorm = min(1, $stats['fill_rate']);
        $ctrNorm = min(1, $stats['ctr'] / 0.05);

        return ($ecpmNorm * $ecpmWeight) + ($fillNorm * $fillWeight) + ($ctrNorm * $ctrWeight);
    }

    private function reorderWaterfall(AdPlacement $placement): void
    {
        $sorted = $placement->mediationWaterfall()
            ->orderByDesc('historical_ecpm')
            ->get();

        $priority = 1;
        foreach ($sorted as $entry) {
            $entry->update(['priority' => $priority++]);
        }
    }
}

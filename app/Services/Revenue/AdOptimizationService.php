<?php

namespace App\Services\Revenue;

use App\Models\AdPlacement;
use App\Models\AdRevenueSnapshot;
use App\Models\RevenueEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdOptimizationService
{
    public function getOptimizationRecommendations(): array
    {
        return [
            'best_placements' => $this->getBestPlacements(),
            'best_frequencies' => $this->getBestFrequencies(),
            'best_networks' => $this->getBestNetworks(),
            'best_formats' => $this->getBestFormats(),
        ];
    }

    public function optimizePlacements(): int
    {
        $optimized = 0;
        $placements = AdPlacement::where('is_enabled', true)->get();

        foreach ($placements as $placement) {
            $performance = $this->getPlacementPerformance($placement->id);
            if (! $performance) {
                continue;
            }

            $optimalFrequency = $this->calculateOptimalFrequency($performance);
            if ($optimalFrequency !== $placement->frequency_cap) {
                $placement->update(['frequency_cap' => $optimalFrequency]);
                $optimized++;
            }
        }

        return $optimized;
    }

    private function getBestPlacements(): array
    {
        return AdRevenueSnapshot::query()
            ->where('date', '>=', now()->subDays(14))
            ->select('ad_placement_id', DB::raw('SUM(revenue) as revenue'), DB::raw('AVG(ecpm) as ecpm'), DB::raw('SUM(impressions) as impressions'))
            ->whereNotNull('ad_placement_id')
            ->groupBy('ad_placement_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'placement_id' => $row->ad_placement_id,
                'placement' => AdPlacement::find($row->ad_placement_id)?->name,
                'revenue' => round((float) $row->revenue, 4),
                'ecpm' => round((float) $row->ecpm, 4),
                'impressions' => (int) $row->impressions,
            ])
            ->toArray();
    }

    private function getBestFrequencies(): array
    {
        $placements = AdPlacement::where('is_enabled', true)->get();

        return $placements->map(function (AdPlacement $placement) {
            $perf = $this->getPlacementPerformance($placement->id);

            return [
                'placement_id' => $placement->id,
                'placement_key' => $placement->placement_key,
                'current_frequency' => $placement->frequency_cap,
                'recommended_frequency' => $perf ? $this->calculateOptimalFrequency($perf) : $placement->frequency_cap,
            ];
        })->toArray();
    }

    private function getBestNetworks(): array
    {
        return AdRevenueSnapshot::query()
            ->where('date', '>=', now()->subDays(14))
            ->select('ad_network', DB::raw('SUM(revenue) as revenue'), DB::raw('AVG(ecpm) as ecpm'), DB::raw('AVG(fill_rate) as fill_rate'))
            ->whereNotNull('ad_network')
            ->groupBy('ad_network')
            ->orderByDesc(DB::raw('AVG(ecpm) * AVG(fill_rate)'))
            ->get()
            ->map(fn ($row) => [
                'network' => $row->ad_network?->value ?? $row->ad_network,
                'revenue' => round((float) $row->revenue, 4),
                'ecpm' => round((float) $row->ecpm, 4),
                'fill_rate' => round((float) $row->fill_rate, 4),
            ])
            ->toArray();
    }

    private function getBestFormats(): array
    {
        return RevenueEvent::query()
            ->where('occurred_at', '>=', now()->subDays(14))
            ->whereNotNull('ad_format')
            ->select('ad_format', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as events'))
            ->groupBy('ad_format')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'format' => $row->ad_format,
                'revenue' => round((float) $row->revenue, 4),
                'events' => (int) $row->events,
            ])
            ->toArray();
    }

    private function getPlacementPerformance(int $placementId): ?object
    {
        return AdRevenueSnapshot::query()
            ->where('ad_placement_id', $placementId)
            ->where('date', '>=', now()->subDays(14))
            ->selectRaw('SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(revenue) as revenue, AVG(ecpm) as ecpm, AVG(ctr) as ctr')
            ->first();
    }

    private function calculateOptimalFrequency(object $performance): int
    {
        $ctr = (float) ($performance->ctr ?? 0);
        $ecpm = (float) ($performance->ecpm ?? 0);
        $min = config('revenue.optimization.frequency_cap_min', 1);
        $max = config('revenue.optimization.frequency_cap_max', 10);

        if ($ctr < 0.005 || $ecpm < 1.0) {
            return max($min, (int) ($max * 0.5));
        }

        if ($ctr > 0.02 && $ecpm > 3.0) {
            return $max;
        }

        return (int) round(($min + $max) / 2);
    }
}

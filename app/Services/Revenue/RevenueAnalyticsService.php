<?php

namespace App\Services\Revenue;

use App\Models\AdRevenueSnapshot;
use App\Models\RevenueEvent;
use App\Models\User;
use App\Repositories\Contracts\AdRevenueSnapshotRepositoryInterface;
use App\Repositories\Contracts\RevenueEventRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueAnalyticsService
{
    public function __construct(
        private readonly RevenueEventRepositoryInterface $revenueEventRepository,
        private readonly AdRevenueSnapshotRepositoryInterface $snapshotRepository,
    ) {
    }

    public function getAnalytics(array $dateRange = []): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        $snapshots = $this->snapshotRepository->getSnapshotsForRange([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);

        $totals = $this->aggregateTotals($snapshots, $from, $to);
        $activeUsers = User::where('last_active_at', '>=', $from)->count();
        $dau = User::where('last_active_at', '>=', now()->subDay())->count();

        return [
            'summary' => [
                'impressions' => $totals['impressions'],
                'clicks' => $totals['clicks'],
                'ctr' => $totals['ctr'],
                'fill_rate' => $totals['fill_rate'],
                'ecpm' => $totals['ecpm'],
                'total_revenue' => $totals['revenue'],
                'arpu' => $activeUsers > 0 ? round($totals['revenue'] / $activeUsers, 4) : 0,
                'arpdau' => $dau > 0 ? round($totals['revenue'] / $dau, 4) : 0,
            ],
            'by_country' => $this->revenueEventRepository->getRevenueByDimension('country', [
                'from' => $from, 'to' => $to,
            ]),
            'by_platform' => $this->revenueEventRepository->getRevenueByDimension('platform', [
                'from' => $from, 'to' => $to,
            ]),
            'by_category' => $this->getRevenueByCategory($from, $to),
            'by_network' => $this->revenueEventRepository->getRevenueByDimension('ad_network', [
                'from' => $from, 'to' => $to,
            ]),
            'daily' => $snapshots->groupBy(fn ($s) => $s->date->toDateString()),
        ];
    }

    public function calculateDailySnapshots(?string $date = null): void
    {
        $date = $date ?? now()->subDay()->toDateString();
        $start = $date.' 00:00:00';
        $end = $date.' 23:59:59';

        $dimensions = RevenueEvent::whereBetween('occurred_at', [$start, $end])
            ->select(
                'ad_network',
                'ad_placement_id',
                'country',
                'platform',
                'category_id',
                DB::raw('COUNT(CASE WHEN event_type = "impression" THEN 1 END) as impressions'),
                DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as clicks'),
                DB::raw('COUNT(*) as requests'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('ad_network', 'ad_placement_id', 'country', 'platform', 'category_id')
            ->get();

        foreach ($dimensions as $row) {
            $impressions = (int) $row->impressions;
            $clicks = (int) $row->clicks;
            $requests = (int) $row->requests;
            $revenue = (float) $row->revenue;

            $this->snapshotRepository->upsertSnapshot([
                'date' => $date,
                'ad_network' => $row->ad_network,
                'ad_placement_id' => $row->ad_placement_id,
                'country' => $row->country,
                'platform' => $row->platform,
                'category_id' => $row->category_id,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'requests' => $requests,
                'revenue' => $revenue,
                'fill_rate' => $requests > 0 ? round($impressions / $requests, 4) : 0,
                'ecpm' => $impressions > 0 ? round(($revenue / $impressions) * 1000, 4) : 0,
                'ctr' => $impressions > 0 ? round($clicks / $impressions, 4) : 0,
            ]);
        }
    }

    private function aggregateTotals($snapshots, Carbon $from, Carbon $to): array
    {
        $impressions = $snapshots->sum('impressions');
        $clicks = $snapshots->sum('clicks');
        $requests = $snapshots->sum('requests');
        $revenue = (float) $snapshots->sum('revenue');

        if ($snapshots->isEmpty()) {
            $live = RevenueEvent::whereBetween('occurred_at', [$from, $to])
                ->selectRaw('COUNT(CASE WHEN event_type = "impression" THEN 1 END) as impressions')
                ->selectRaw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as clicks')
                ->selectRaw('COUNT(*) as requests')
                ->selectRaw('SUM(amount) as revenue')
                ->first();

            $impressions = (int) ($live->impressions ?? 0);
            $clicks = (int) ($live->clicks ?? 0);
            $requests = (int) ($live->requests ?? 0);
            $revenue = (float) ($live->revenue ?? 0);
        }

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'requests' => $requests,
            'revenue' => round($revenue, 4),
            'ctr' => $impressions > 0 ? round($clicks / $impressions, 4) : 0,
            'fill_rate' => $requests > 0 ? round($impressions / $requests, 4) : 0,
            'ecpm' => $impressions > 0 ? round(($revenue / $impressions) * 1000, 4) : 0,
        ];
    }

    private function getRevenueByCategory(Carbon $from, Carbon $to): array
    {
        return DB::table('revenue_events')
            ->join('categories', 'revenue_events.category_id', '=', 'categories.id')
            ->whereBetween('revenue_events.occurred_at', [$from, $to])
            ->whereNotNull('revenue_events.category_id')
            ->select('categories.id', 'categories.name', DB::raw('SUM(revenue_events.amount) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }
}

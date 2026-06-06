<?php

namespace App\Services\NotificationIntelligence;

use App\Models\Notification;
use App\Models\NotificationAnalyticsSnapshot;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationIntelligenceAnalyticsService
{
    public function calculateDailySnapshot(?string $date = null): void
    {
        $date = $date ?? now()->toDateString();
        $types = ['manual', 'breaking', 'digest', 'recommendation', 'automated', null];

        foreach ($types as $type) {
            $this->snapshotForType($date, $type);
        }
    }

    public function getAnalytics(array $dateRange = []): array
    {
        $from = $dateRange['from'] ?? now()->subDays(30)->toDateString();
        $to = $dateRange['to'] ?? now()->toDateString();

        $snapshots = NotificationAnalyticsSnapshot::whereBetween('date', [$from, $to])->get();

        $overall = $this->calculateLiveMetrics($from, $to);

        return [
            'overview' => $overall,
            'daily' => $snapshots->groupBy('date'),
            'by_type' => $snapshots->groupBy('notification_type'),
            'averages' => [
                'delivery_rate' => round($snapshots->avg('delivery_rate') ?? 0, 4),
                'open_rate' => round($snapshots->avg('open_rate') ?? 0, 4),
                'ctr' => round($snapshots->avg('ctr') ?? 0, 4),
                'conversion_rate' => round($snapshots->avg('conversion_rate') ?? 0, 4),
                'retention_impact' => round($snapshots->avg('retention_impact') ?? 0, 4),
            ],
        ];
    }

    private function snapshotForType(string $date, ?string $type): void
    {
        $start = $date.' 00:00:00';
        $end = $date.' 23:59:59';

        $query = Notification::whereBetween('sent_at', [$start, $end])->whereNotNull('sent_at');
        if ($type) {
            $query->where('notification_type', $type);
        }

        $notifications = $query->get();
        $notificationIds = $notifications->pluck('id');

        $totalSent = $notifications->sum('total_recipients');
        $totalDelivered = $notifications->sum('delivered_count');
        $totalOpened = $notifications->sum('opened_count');

        $deliveries = NotificationDelivery::whereIn('notification_id', $notificationIds)->get();
        $clicked = $deliveries->whereNotNull('opened_at')->count();
        $read = $deliveries->where('status', 'delivered')->whereNotNull('opened_at')->count();

        $deliveryRate = $totalSent > 0 ? $totalDelivered / $totalSent : 0;
        $openRate = $totalDelivered > 0 ? $totalOpened / $totalDelivered : 0;
        $ctr = $totalDelivered > 0 ? $clicked / $totalDelivered : 0;
        $conversionRate = $clicked > 0 ? $read / $clicked : 0;
        $retentionImpact = $this->calculateRetentionImpact($date);

        NotificationAnalyticsSnapshot::updateOrCreate(
            ['date' => $date, 'notification_type' => $type],
            [
                'delivery_rate' => round($deliveryRate, 4),
                'open_rate' => round($openRate, 4),
                'ctr' => round($ctr, 4),
                'conversion_rate' => round($conversionRate, 4),
                'retention_impact' => round($retentionImpact, 4),
                'total_sent' => $totalSent,
                'total_delivered' => $totalDelivered,
                'total_opened' => $totalOpened,
                'total_clicked' => $clicked,
            ]
        );
    }

    private function calculateLiveMetrics(string $from, string $to): array
    {
        $notifications = Notification::whereBetween('sent_at', [$from, $to])->whereNotNull('sent_at')->get();

        $totalSent = $notifications->sum('total_recipients');
        $totalDelivered = $notifications->sum('delivered_count');
        $totalOpened = $notifications->sum('opened_count');

        return [
            'total_campaigns' => $notifications->count(),
            'total_sent' => $totalSent,
            'total_delivered' => $totalDelivered,
            'total_opened' => $totalOpened,
            'delivery_rate' => $totalSent > 0 ? round($totalDelivered / $totalSent, 4) : 0,
            'open_rate' => $totalDelivered > 0 ? round($totalOpened / $totalDelivered, 4) : 0,
        ];
    }

    private function calculateRetentionImpact(string $date): float
    {
        $day = \Carbon\Carbon::parse($date);
        $notifiedUsers = NotificationDelivery::whereDate('delivered_at', $day)
            ->distinct('user_id')
            ->pluck('user_id');

        if ($notifiedUsers->isEmpty()) {
            return 0;
        }

        $retained = User::whereIn('id', $notifiedUsers)
            ->where('last_active_at', '>=', $day->copy()->addDay())
            ->count();

        return $retained / $notifiedUsers->count();
    }
}

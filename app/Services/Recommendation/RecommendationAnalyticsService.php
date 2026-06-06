<?php

namespace App\Services\Recommendation;

use App\Models\RecommendationAnalyticsSnapshot;
use App\Models\RecommendationLog;
use App\Models\User;
use App\Models\UserBehaviorEvent;
use App\Enums\BehaviorEventType;
use Illuminate\Support\Facades\DB;

class RecommendationAnalyticsService
{
    public function calculateDailySnapshot(?string $date = null): void
    {
        $date = $date ?? now()->toDateString();
        $feedTypes = ['for_you', 'following', 'trending', 'breaking', 'local', null];

        foreach ($feedTypes as $feedType) {
            $this->snapshotForFeedType($date, $feedType);
        }
    }

    public function getMetrics(array $dateRange = []): array
    {
        $from = $dateRange['from'] ?? now()->subDays(30)->toDateString();
        $to = $dateRange['to'] ?? now()->toDateString();

        $snapshots = RecommendationAnalyticsSnapshot::whereBetween('date', [$from, $to])->get();

        return [
            'ctr' => round($snapshots->avg('ctr') ?? 0, 4),
            'read_completion_rate' => round($snapshots->avg('read_completion_rate') ?? 0, 4),
            'retention_rate' => round($snapshots->avg('retention_rate') ?? 0, 4),
            'avg_session_duration_seconds' => (int) ($snapshots->avg('avg_session_duration_seconds') ?? 0),
            'recommendation_accuracy' => round($snapshots->avg('recommendation_accuracy') ?? 0, 4),
            'daily' => $snapshots->groupBy('date'),
        ];
    }

    private function snapshotForFeedType(string $date, ?string $feedType): void
    {
        $start = $date.' 00:00:00';
        $end = $date.' 23:59:59';

        $query = RecommendationLog::whereBetween('served_at', [$start, $end]);
        if ($feedType) {
            $query->where('feed_type', $feedType);
        }

        $impressions = (clone $query)->count();
        $clicks = (clone $query)->where('was_clicked', true)->count();
        $reads = (clone $query)->where('was_read', true)->count();

        $ctr = $impressions > 0 ? $clicks / $impressions : 0;
        $readCompletion = $clicks > 0 ? $reads / $clicks : 0;

        $avgReadTime = RecommendationLog::whereBetween('served_at', [$start, $end])
            ->whereNotNull('read_time_seconds')
            ->avg('read_time_seconds') ?? 0;

        $readCompletionRate = $avgReadTime > 30 ? min(1.0, $avgReadTime / 120) : $readCompletion;

        $retention = $this->calculateRetention($date);
        $accuracy = $this->calculateAccuracy($date, $feedType);

        $avgSessionDuration = $this->calculateAvgSessionDuration($date);

        RecommendationAnalyticsSnapshot::updateOrCreate(
            ['date' => $date, 'feed_type' => $feedType],
            [
                'ctr' => round($ctr, 4),
                'read_completion_rate' => round($readCompletionRate, 4),
                'retention_rate' => round($retention, 4),
                'avg_session_duration_seconds' => $avgSessionDuration,
                'recommendation_accuracy' => round($accuracy, 4),
                'impressions' => $impressions,
                'clicks' => $clicks,
            ]
        );
    }

    private function calculateRetention(string $date): float
    {
        $day = \Carbon\Carbon::parse($date);
        $cohortUsers = User::whereDate('created_at', $day->copy()->subDay())->pluck('id');
        if ($cohortUsers->isEmpty()) {
            return 0;
        }

        $retained = User::whereIn('id', $cohortUsers)
            ->where('last_active_at', '>=', $day)
            ->count();

        return $retained / $cohortUsers->count();
    }

    private function calculateAccuracy(string $date, ?string $feedType): float
    {
        $start = $date.' 00:00:00';
        $end = $date.' 23:59:59';

        $query = RecommendationLog::whereBetween('served_at', [$start, $end])
            ->where('was_clicked', true);

        if ($feedType) {
            $query->where('feed_type', $feedType);
        }

        $clicked = $query->count();
        if ($clicked === 0) {
            return 0;
        }

        $engaged = (clone $query)->where(function ($q) {
            $q->where('was_read', true)->orWhere('read_time_seconds', '>', 15);
        })->count();

        return $engaged / $clicked;
    }

    private function calculateAvgSessionDuration(string $date): int
    {
        $sessions = UserBehaviorEvent::whereDate('occurred_at', $date)
            ->where('event_type', BehaviorEventType::SessionStart)
            ->distinct('session_id')
            ->pluck('session_id');

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalDuration = 0;
        $count = 0;

        foreach ($sessions as $sessionId) {
            $start = UserBehaviorEvent::where('session_id', $sessionId)
                ->where('event_type', BehaviorEventType::SessionStart)
                ->value('occurred_at');
            $end = UserBehaviorEvent::where('session_id', $sessionId)
                ->where('event_type', BehaviorEventType::SessionEnd)
                ->value('occurred_at');

            if ($start) {
                $totalDuration += ($end ?? now())->diffInSeconds($start);
                $count++;
            }
        }

        return $count > 0 ? (int) ($totalDuration / $count) : 0;
    }
}

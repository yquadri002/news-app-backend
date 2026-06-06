<?php

namespace App\Services\Revenue;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\UserMonetizationProfile;
use App\Repositories\Contracts\GrowthMetricRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GrowthAnalyticsService
{
    public function __construct(
        private readonly GrowthMetricRepositoryInterface $growthMetricRepository,
    ) {
    }

    public function calculateDailyMetrics(?string $date = null): void
    {
        $date = $date ?? now()->subDay()->toDateString();
        $day = Carbon::parse($date);

        $dau = User::whereDate('last_active_at', $day)->count();
        $wau = User::where('last_active_at', '>=', $day->copy()->subDays(7))->count();
        $mau = User::where('last_active_at', '>=', $day->copy()->subDays(30))->count();
        $newUsers = User::whereDate('created_at', $day)->count();

        $this->growthMetricRepository->upsertForDate($date, [
            'dau' => $dau,
            'wau' => $wau,
            'mau' => $mau,
            'new_users' => $newUsers,
            'retention_d1' => $this->calculateRetention($day, 1),
            'retention_d7' => $this->calculateRetention($day, 7),
            'retention_d30' => $this->calculateRetention($day, 30),
            'avg_session_length' => $this->calculateAvgSessionLength($day),
            'avg_ltv' => $this->calculateAvgLtv(),
        ]);
    }

    public function getGrowthMetrics(array $dateRange = []): array
    {
        $metrics = $this->growthMetricRepository->getRange([
            'from' => $dateRange['from'] ?? now()->subDays(30)->toDateString(),
            'to' => $dateRange['to'] ?? now()->toDateString(),
        ]);

        $latest = $metrics->last();

        return [
            'current' => [
                'dau' => $latest?->dau ?? User::where('last_active_at', '>=', now()->subDay())->count(),
                'wau' => $latest?->wau ?? User::where('last_active_at', '>=', now()->subDays(7))->count(),
                'mau' => $latest?->mau ?? User::where('last_active_at', '>=', now()->subDays(30))->count(),
                'retention_d1' => (float) ($latest?->retention_d1 ?? 0),
                'retention_d7' => (float) ($latest?->retention_d7 ?? 0),
                'retention_d30' => (float) ($latest?->retention_d30 ?? 0),
                'avg_session_length' => (float) ($latest?->avg_session_length ?? 0),
                'avg_ltv' => (float) ($latest?->avg_ltv ?? 0),
            ],
            'daily' => $metrics,
        ];
    }

    public function calculateUserLtv(int $userId): float
    {
        $profile = UserMonetizationProfile::where('user_id', $userId)->first();
        if ($profile) {
            return (float) $profile->lifetime_value;
        }

        $revenue = DB::table('revenue_events')
            ->where('user_id', $userId)
            ->sum('amount');

        $daysActive = max(1, User::find($userId)?->created_at?->diffInDays(now()) ?? 1);
        $dailyValue = $revenue / $daysActive;
        $predictionDays = config('revenue.growth.ltv_prediction_days', 365);

        return round($dailyValue * $predictionDays, 4);
    }

    public function calculateAllLtv(): int
    {
        $count = 0;
        User::where('last_active_at', '>=', now()->subDays(90))
            ->chunkById(100, function ($users) use (&$count) {
                foreach ($users as $user) {
                    $ltv = $this->calculateUserLtv($user->id);
                    UserMonetizationProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['lifetime_value' => $ltv, 'last_calculated_at' => now()]
                    );
                    $count++;
                }
            });

        return $count;
    }

    private function calculateRetention(Carbon $cohortDate, int $dayNumber): float
    {
        $cohortUsers = User::whereDate('created_at', $cohortDate->copy()->subDays($dayNumber))->pluck('id');
        if ($cohortUsers->isEmpty()) {
            return 0;
        }

        $retained = User::whereIn('id', $cohortUsers)
            ->where('last_active_at', '>=', $cohortDate)
            ->count();

        return round($retained / $cohortUsers->count(), 4);
    }

    private function calculateAvgSessionLength(Carbon $day): float
    {
        $sessions = AnalyticsEvent::whereDate('occurred_at', $day)
            ->where('event_type', 'session_end')
            ->get()
            ->map(fn ($e) => $e->metadata['duration_seconds'] ?? 0)
            ->filter(fn ($d) => $d > 0);

        return $sessions->isEmpty() ? 0 : round($sessions->avg(), 2);
    }

    private function calculateAvgLtv(): float
    {
        $avg = UserMonetizationProfile::avg('lifetime_value');

        return round((float) ($avg ?? 0), 4);
    }
}

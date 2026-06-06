<?php

namespace App\Services\Revenue;

use App\Enums\MonetizationSegment;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use App\Models\UserMonetizationProfile;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

class UserMonetizationSegmentationService
{
    public function segmentUser(User $user): UserMonetizationProfile
    {
        $profile = $this->buildProfileData($user);
        $segment = $this->determineSegment($user, $profile);

        return UserMonetizationProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($profile, [
                'segment' => $segment,
                'last_calculated_at' => now(),
            ])
        );
    }

    public function segmentAllActiveUsers(): int
    {
        $count = 0;
        User::where('last_active_at', '>=', now()->subDays(30))
            ->chunkById(100, function ($users) use (&$count) {
                foreach ($users as $user) {
                    $this->segmentUser($user);
                    $count++;
                }
            });

        return $count;
    }

    public function getSegmentDistribution(): array
    {
        return UserMonetizationProfile::query()
            ->select('segment', DB::raw('COUNT(*) as count'))
            ->groupBy('segment')
            ->get()
            ->map(fn ($row) => [
                'segment' => $row->segment?->value ?? $row->segment,
                'label' => MonetizationSegment::tryFrom($row->segment?->value ?? $row->segment)?->label() ?? $row->segment,
                'count' => (int) $row->count,
            ])
            ->toArray();
    }

    private function buildProfileData(User $user): array
    {
        $events = DB::table('revenue_events')
            ->where('user_id', $user->id)
            ->selectRaw('SUM(CASE WHEN event_type = "impression" THEN 1 ELSE 0 END) as impressions')
            ->selectRaw('SUM(CASE WHEN event_type = "click" THEN 1 ELSE 0 END) as clicks')
            ->selectRaw('SUM(CASE WHEN event_type IN ("impression","click") THEN amount ELSE 0 END) as ad_revenue')
            ->selectRaw('SUM(CASE WHEN event_type IN ("subscription","purchase","renewal") THEN amount ELSE 0 END) as sub_revenue')
            ->first();

        $articlesRead = DB::table('user_behavior_events')
            ->where('user_id', $user->id)
            ->where('event_type', 'article_open')
            ->count();

        $impressions = (int) ($events->impressions ?? 0);
        $clicks = (int) ($events->clicks ?? 0);
        $adRevenue = (float) ($events->ad_revenue ?? 0);
        $subRevenue = (float) ($events->sub_revenue ?? 0);

        return [
            'lifetime_value' => round($adRevenue + $subRevenue, 4),
            'total_ad_revenue' => round($adRevenue, 4),
            'total_subscription_revenue' => round($subRevenue, 4),
            'ad_impressions' => $impressions,
            'ad_clicks' => $clicks,
            'articles_read' => $articlesRead,
            'ad_sensitivity_score' => $impressions > 0 ? round(1 - ($clicks / $impressions), 4) : 0.5,
        ];
    }

    private function determineSegment(User $user, array $profile): MonetizationSegment
    {
        $hasPremium = UserSubscription::where('user_id', $user->id)
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->exists();

        if ($hasPremium) {
            return MonetizationSegment::Premium;
        }

        $highThreshold = config('revenue.segmentation.high_revenue_threshold', 5.0);
        $lowThreshold = config('revenue.segmentation.low_revenue_threshold', 0.50);
        $heavyThreshold = config('revenue.segmentation.heavy_reader_articles', 50);
        $casualThreshold = config('revenue.segmentation.casual_reader_articles', 10);
        $ctrThreshold = config('revenue.segmentation.ad_sensitive_ctr_threshold', 0.01);

        if ($profile['lifetime_value'] >= $highThreshold) {
            return MonetizationSegment::HighRevenue;
        }

        if ($profile['ad_impressions'] > 100) {
            $ctr = $profile['ad_impressions'] > 0 ? $profile['ad_clicks'] / $profile['ad_impressions'] : 0;
            if ($ctr < $ctrThreshold) {
                return MonetizationSegment::AdSensitive;
            }
        }

        if ($profile['articles_read'] >= $heavyThreshold) {
            return MonetizationSegment::HeavyReader;
        }

        if ($profile['articles_read'] <= $casualThreshold && $profile['lifetime_value'] < $lowThreshold) {
            return MonetizationSegment::CasualReader;
        }

        if ($profile['lifetime_value'] < $lowThreshold) {
            return MonetizationSegment::LowRevenue;
        }

        return MonetizationSegment::CasualReader;
    }
}

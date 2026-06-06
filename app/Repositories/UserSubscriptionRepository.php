<?php

namespace App\Repositories;

use App\Enums\SubscriptionStatus;
use App\Models\UserSubscription;
use App\Repositories\Contracts\UserSubscriptionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserSubscriptionRepository extends BaseRepository implements UserSubscriptionRepositoryInterface
{
    public function __construct(UserSubscription $model)
    {
        parent::__construct($model);
    }

    public function getActiveForUser(int $userId): ?UserSubscription
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->where(function (Builder $q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest('starts_at')
            ->first();
    }

    public function getSubscriptionMetrics(array $dateRange): array
    {
        $from = Carbon::parse($dateRange['from'] ?? now()->subDays(30));
        $to = Carbon::parse($dateRange['to'] ?? now());

        $active = $this->query()
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->count();

        $trials = $this->query()
            ->where('status', SubscriptionStatus::Trialing)
            ->whereBetween('starts_at', [$from, $to])
            ->count();

        $conversions = $this->query()
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('trial_ends_at')
            ->whereBetween('starts_at', [$from, $to])
            ->count();

        $churned = $this->query()
            ->where('status', SubscriptionStatus::Cancelled)
            ->whereBetween('cancelled_at', [$from, $to])
            ->count();

        $trialConversionRate = $trials > 0 ? round($conversions / $trials, 4) : 0;
        $churnRate = $active > 0 ? round($churned / ($active + $churned), 4) : 0;

        $retention = $this->query()
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->where('starts_at', '<=', now()->subDays(30))
            ->count();

        $retained = $this->query()
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->where('starts_at', '<=', now()->subDays(30))
            ->where(function (Builder $q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->count();

        return [
            'active_subscribers' => $active,
            'trials_started' => $trials,
            'trial_conversions' => $conversions,
            'trial_conversion_rate' => $trialConversionRate,
            'churned' => $churned,
            'churn_rate' => $churnRate,
            'subscriber_retention_30d' => $retention > 0 ? round($retained / $retention, 4) : 0,
            'mrr' => $this->calculateMrr(),
        ];
    }

    private function calculateMrr(): float
    {
        return (float) DB::table('user_subscriptions')
            ->join('subscription_plans', 'user_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->whereIn('user_subscriptions.status', ['active', 'trialing'])
            ->selectRaw("SUM(CASE WHEN subscription_plans.billing_period = 'yearly' THEN subscription_plans.price / 12 ELSE subscription_plans.price END) as mrr")
            ->value('mrr') ?? 0;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->with('plan')->latest('starts_at');
    }
}

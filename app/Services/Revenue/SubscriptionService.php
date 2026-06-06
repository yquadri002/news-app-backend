<?php

namespace App\Services\Revenue;

use App\Enums\RevenueEventType;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Repositories\Contracts\RevenueEventRepositoryInterface;
use App\Repositories\Contracts\SubscriptionPlanRepositoryInterface;
use App\Repositories\Contracts\UserSubscriptionRepositoryInterface;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionPlanRepositoryInterface $planRepository,
        private readonly UserSubscriptionRepositoryInterface $subscriptionRepository,
        private readonly RevenueEventRepositoryInterface $revenueEventRepository,
    ) {
    }

    public function getPlans()
    {
        return $this->planRepository->getActivePlans();
    }

    public function subscribe(User $user, int $planId, array $data = []): UserSubscription
    {
        $plan = $this->planRepository->findOrFail($planId);
        $existing = $this->subscriptionRepository->getActiveForUser($user->id);

        if ($existing) {
            $existing->update(['status' => SubscriptionStatus::Cancelled, 'cancelled_at' => now()]);
        }

        $startsAt = now();
        $trialEndsAt = $plan->trial_days > 0 ? $startsAt->copy()->addDays($plan->trial_days) : null;
        $status = $trialEndsAt ? SubscriptionStatus::Trialing : SubscriptionStatus::Active;

        $subscription = $this->subscriptionRepository->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => $status,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => $startsAt,
            'ends_at' => $this->calculateEndDate($plan, $startsAt),
            'platform' => $data['platform'] ?? $user->platform,
            'store_transaction_id' => $data['store_transaction_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        $this->revenueEventRepository->recordEvent([
            'user_id' => $user->id,
            'event_type' => $trialEndsAt ? RevenueEventType::TrialStart : RevenueEventType::Subscription,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'platform' => $user->platform,
            'metadata' => ['plan_id' => $plan->id, 'subscription_id' => $subscription->id],
            'occurred_at' => now(),
        ]);

        return $subscription->load('plan');
    }

    public function cancel(UserSubscription $subscription): UserSubscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return $subscription->fresh();
    }

    public function getMetrics(array $dateRange = []): array
    {
        return $this->subscriptionRepository->getSubscriptionMetrics($dateRange);
    }

    public function getSubscriptions(array $filters = [], int $perPage = 20)
    {
        return $this->subscriptionRepository->paginate($perPage, $filters);
    }

    private function calculateEndDate(SubscriptionPlan $plan, $startsAt): ?\Carbon\Carbon
    {
        return match ($plan->billing_period) {
            'monthly' => $startsAt->copy()->addMonth(),
            'yearly' => $startsAt->copy()->addYear(),
            default => null,
        };
    }
}

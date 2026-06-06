<?php

namespace App\Services\Revenue;

use App\Models\RevenueEvent;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RevenueEventVerifier
{
    public function assertAllowed(User $user, array $data): void
    {
        if ($this->userHasActiveSubscription($user)) {
            throw ValidationException::withMessages([
                'event_type' => ['Ad revenue events are not accepted for subscribed users.'],
            ]);
        }

        $perMinuteKey = "revenue-events:{$user->id}";
        $limit = (int) config('revenue.events.max_per_minute', 60);

        if (RateLimiter::tooManyAttempts($perMinuteKey, $limit)) {
            throw ValidationException::withMessages([
                'event_type' => ['Too many revenue events. Please slow down.'],
            ]);
        }

        RateLimiter::hit($perMinuteKey, 60);

        if (! empty($data['amount']) && (float) $data['amount'] > (float) config('revenue.events.max_amount', 1.0)) {
            throw ValidationException::withMessages([
                'amount' => ['Reported amount exceeds the allowed maximum.'],
            ]);
        }

        if (! empty($data['ad_placement_id'])) {
            $this->assertNotDuplicate($user->id, $data);
        }
    }

    private function userHasActiveSubscription(User $user): bool
    {
        return UserSubscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    private function assertNotDuplicate(int $userId, array $data): void
    {
        $exists = RevenueEvent::where('user_id', $userId)
            ->where('event_type', $data['event_type'])
            ->where('ad_placement_id', $data['ad_placement_id'])
            ->where('occurred_at', '>=', now()->subMinute())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'event_type' => ['Duplicate revenue event detected.'],
            ]);
        }
    }
}

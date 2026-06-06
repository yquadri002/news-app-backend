<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionPlanType;
use App\Models\AdPlacement;
use App\Models\RevenueEvent;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RevenueEventTest extends TestCase
{
    public function test_revenue_event_is_recorded_for_free_users(): void
    {
        $context = $this->createAuthenticatedUser();
        $placement = $this->createPlacement();

        $this->withToken($context['token'])
            ->postJson('/api/v1/client/revenue/events', [
                'event_type' => 'impression',
                'ad_placement_id' => $placement->id,
                'amount' => 0.01,
            ])
            ->assertOk();
    }

    public function test_revenue_events_are_rejected_for_subscribed_users(): void
    {
        $context = $this->createAuthenticatedUser();
        $plan = SubscriptionPlan::create([
            'name' => 'Ad-Free',
            'slug' => 'ad-free-test',
            'plan_type' => SubscriptionPlanType::AdFree,
            'billing_period' => 'monthly',
            'price' => 4.99,
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        UserSubscription::create([
            'user_id' => $context['user']->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->withToken($context['token'])
            ->postJson('/api/v1/client/revenue/events', [
                'event_type' => 'impression',
                'ad_placement_id' => $this->createPlacement()->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);
    }

    public function test_duplicate_revenue_events_are_rejected(): void
    {
        $context = $this->createAuthenticatedUser();
        $placement = $this->createPlacement();

        RevenueEvent::create([
            'user_id' => $context['user']->id,
            'event_type' => 'impression',
            'ad_placement_id' => $placement->id,
            'amount' => 0.01,
            'occurred_at' => now(),
        ]);

        $this->withToken($context['token'])
            ->postJson('/api/v1/client/revenue/events', [
                'event_type' => 'impression',
                'ad_placement_id' => $placement->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_type']);
    }

    public function test_revenue_event_amount_above_max_is_rejected(): void
    {
        Config::set('revenue.events.max_amount', 0.5);
        $context = $this->createAuthenticatedUser();

        $this->withToken($context['token'])
            ->postJson('/api/v1/client/revenue/events', [
                'event_type' => 'click',
                'amount' => 1.5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    private function createAuthenticatedUser(): array
    {
        $user = User::factory()->create(['platform' => 'android']);
        $user->preferences()->create(['language' => 'en']);
        $token = $user->createToken('access', ['access'], now()->addHour())->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    private function createPlacement(): AdPlacement
    {
        return AdPlacement::create([
            'name' => 'Test Banner',
            'placement_key' => 'test-banner-'.uniqid(),
            'format' => 'banner',
            'frequency_cap' => 3,
            'frequency_period_minutes' => 60,
            'sort_order' => 1,
            'is_enabled' => true,
        ]);
    }
}

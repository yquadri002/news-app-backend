<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionPlanType;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Tests\TestCase;

class SubscriptionReceiptTest extends TestCase
{
    public function test_subscription_rejects_invalid_transaction_format(): void
    {
        $user = $this->createAuthenticatedUser('ios');
        $plan = $this->createPlan();

        $this->withToken($user['token'])
            ->postJson('/api/v1/client/revenue/subscribe', [
                'plan_id' => $plan->id,
                'store_transaction_id' => 'bad',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_transaction_id']);
    }

    public function test_subscription_rejects_duplicate_transaction_id(): void
    {
        $user = $this->createAuthenticatedUser('ios');
        $plan = $this->createPlan();
        $transactionId = '1000000123456789.ABCD';

        UserSubscription::create([
            'user_id' => User::factory()->create()->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'store_transaction_id' => $transactionId,
        ]);

        $this->withToken($user['token'])
            ->postJson('/api/v1/client/revenue/subscribe', [
                'plan_id' => $plan->id,
                'store_transaction_id' => $transactionId,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['store_transaction_id']);
    }

    public function test_subscription_accepts_valid_transaction_id(): void
    {
        $user = $this->createAuthenticatedUser('ios');
        $plan = $this->createPlan();

        $this->withToken($user['token'])
            ->postJson('/api/v1/client/revenue/subscribe', [
                'plan_id' => $plan->id,
                'store_transaction_id' => '1000000123456789.ABCD',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Subscription activated.');

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user['user']->id,
            'store_transaction_id' => '1000000123456789.ABCD',
        ]);
    }

    private function createAuthenticatedUser(string $platform): array
    {
        $user = User::factory()->create(['platform' => $platform]);
        $user->preferences()->create(['language' => 'en']);
        $token = $user->createToken('access', ['access'], now()->addHour())->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    private function createPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => 'Premium Monthly',
            'slug' => 'premium-monthly-test',
            'plan_type' => SubscriptionPlanType::Premium,
            'billing_period' => 'monthly',
            'price' => 9.99,
            'currency' => 'USD',
            'trial_days' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}

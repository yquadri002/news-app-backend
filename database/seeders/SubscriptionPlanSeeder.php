<?php

namespace Database\Seeders;

use App\Enums\SubscriptionPlanType;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Premium Monthly',
                'slug' => 'premium-monthly',
                'plan_type' => SubscriptionPlanType::Premium,
                'billing_period' => 'monthly',
                'price' => 9.99,
                'trial_days' => 7,
                'features' => ['ad_free', 'exclusive_content', 'offline_reading'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Premium Yearly',
                'slug' => 'premium-yearly',
                'plan_type' => SubscriptionPlanType::Yearly,
                'billing_period' => 'yearly',
                'price' => 79.99,
                'trial_days' => 14,
                'features' => ['ad_free', 'exclusive_content', 'offline_reading', 'priority_support'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Ad-Free Monthly',
                'slug' => 'ad-free-monthly',
                'plan_type' => SubscriptionPlanType::AdFree,
                'billing_period' => 'monthly',
                'price' => 4.99,
                'trial_days' => 3,
                'features' => ['ad_free'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Ad-Free Yearly',
                'slug' => 'ad-free-yearly',
                'plan_type' => SubscriptionPlanType::AdFree,
                'billing_period' => 'yearly',
                'price' => 39.99,
                'trial_days' => 7,
                'features' => ['ad_free'],
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}

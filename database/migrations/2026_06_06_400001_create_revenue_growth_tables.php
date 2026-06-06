<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan_type');
            $table->string('billing_period')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('platform')->nullable();
            $table->string('store_transaction_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'ends_at']);
        });

        Schema::create('revenue_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('ad_network')->nullable();
            $table->foreignId('ad_placement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 6)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('country', 5)->nullable();
            $table->string('platform')->nullable();
            $table->string('ad_format')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['ad_network', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['country', 'occurred_at']);
            $table->index(['platform', 'occurred_at']);
        });

        Schema::create('ad_revenue_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('ad_network')->nullable();
            $table->foreignId('ad_placement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('country', 5)->nullable();
            $table->string('platform')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('requests')->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->decimal('fill_rate', 8, 4)->default(0);
            $table->decimal('ecpm', 10, 4)->default(0);
            $table->decimal('ctr', 8, 4)->default(0);
            $table->timestamps();

            $table->unique(['date', 'ad_network', 'ad_placement_id', 'country', 'platform', 'category_id'], 'ad_revenue_snapshots_unique');
        });

        Schema::create('growth_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedBigInteger('dau')->default(0);
            $table->unsignedBigInteger('wau')->default(0);
            $table->unsignedBigInteger('mau')->default(0);
            $table->unsignedBigInteger('new_users')->default(0);
            $table->decimal('retention_d1', 8, 4)->default(0);
            $table->decimal('retention_d7', 8, 4)->default(0);
            $table->decimal('retention_d30', 8, 4)->default(0);
            $table->decimal('avg_session_length', 10, 2)->default(0);
            $table->decimal('avg_ltv', 12, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('ab_test_results', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('test_type');
            $table->json('variants');
            $table->string('status')->default('active');
            $table->string('winning_variant')->nullable();
            $table->json('metrics')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['test_type', 'status']);
        });

        Schema::create('user_monetization_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('segment')->default('casual_reader');
            $table->decimal('lifetime_value', 12, 4)->default(0);
            $table->decimal('total_ad_revenue', 12, 4)->default(0);
            $table->decimal('total_subscription_revenue', 12, 4)->default(0);
            $table->unsignedInteger('ad_impressions')->default(0);
            $table->unsignedInteger('ad_clicks')->default(0);
            $table->unsignedInteger('articles_read')->default(0);
            $table->decimal('ad_sensitivity_score', 5, 4)->default(0.5);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index('segment');
        });

        Schema::create('ad_mediation_waterfalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_placement_id')->constrained()->cascadeOnDelete();
            $table->string('ad_network');
            $table->unsignedTinyInteger('priority')->default(1);
            $table->decimal('floor_price', 10, 4)->default(0);
            $table->decimal('historical_ecpm', 10, 4)->default(0);
            $table->decimal('fill_rate', 8, 4)->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['ad_placement_id', 'ad_network']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_mediation_waterfalls');
        Schema::dropIfExists('user_monetization_profiles');
        Schema::dropIfExists('ab_test_results');
        Schema::dropIfExists('growth_metrics');
        Schema::dropIfExists('ad_revenue_snapshots');
        Schema::dropIfExists('revenue_events');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->index(['article_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
        });

        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('query');
            $table->unsignedInteger('results_count')->default(0);
            $table->string('device_id')->nullable();
            $table->timestamps();

            $table->index(['query', 'created_at']);
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
        });

        Schema::create('category_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('article_opens')->default(0);
            $table->unsignedBigInteger('unique_users')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'date']);
        });

        Schema::create('user_retention_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('cohort_date');
            $table->unsignedTinyInteger('day_number');
            $table->unsignedBigInteger('cohort_size')->default(0);
            $table->unsignedBigInteger('retained_users')->default(0);
            $table->decimal('retention_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['cohort_date', 'day_number']);
        });

        Schema::create('revenue_records', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('USD');
            $table->date('recorded_date');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['source', 'recorded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_records');
        Schema::dropIfExists('user_retention_snapshots');
        Schema::dropIfExists('category_analytics');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('search_analytics');
        Schema::dropIfExists('article_views');
    }
};

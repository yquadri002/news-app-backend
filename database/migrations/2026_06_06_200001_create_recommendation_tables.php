<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_behavior_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id')->nullable();
            $table->string('event_type');
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rss_source_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('read_time_seconds')->nullable();
            $table->unsignedTinyInteger('scroll_depth_percent')->nullable();
            $table->string('search_query')->nullable();
            $table->string('session_id')->nullable();
            $table->string('feed_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['user_id', 'event_type', 'occurred_at']);
            $table->index(['article_id', 'event_type']);
            $table->index('occurred_at');
        });

        Schema::create('user_interest_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_cold_start')->default(true);
            $table->string('primary_segment')->nullable();
            $table->json('top_topics')->nullable();
            $table->decimal('profile_strength', 5, 4)->default(0);
            $table->unsignedInteger('total_events')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_category_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 4)->default(0);
            $table->decimal('explicit_score', 8, 4)->default(0);
            $table->decimal('implicit_score', 8, 4)->default(0);
            $table->unsignedInteger('interaction_count')->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'category_id']);
            $table->index(['user_id', 'score']);
        });

        Schema::create('user_source_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rss_source_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 4)->default(0);
            $table->decimal('explicit_score', 8, 4)->default(0);
            $table->decimal('implicit_score', 8, 4)->default(0);
            $table->unsignedInteger('interaction_count')->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'rss_source_id']);
            $table->index(['user_id', 'score']);
        });

        Schema::create('user_topic_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->decimal('score', 8, 4)->default(0);
            $table->unsignedInteger('interaction_count')->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'topic']);
            $table->index(['user_id', 'score']);
        });

        Schema::create('user_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'article_id']);
        });

        Schema::create('recommendation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feed_type');
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->decimal('rank_score', 10, 4)->default(0);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('was_clicked')->default(false);
            $table->boolean('was_read')->default(false);
            $table->unsignedInteger('read_time_seconds')->nullable();
            $table->string('session_id')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->timestamp('served_at');
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'feed_type', 'served_at']);
            $table->index(['article_id', 'was_clicked']);
        });

        Schema::create('user_segment_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_segment_id')->constrained()->cascadeOnDelete();
            $table->decimal('confidence', 5, 4)->default(0);
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->unique(['user_id', 'user_segment_id']);
        });

        Schema::create('recommendation_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('feed_type')->nullable();
            $table->decimal('ctr', 5, 4)->default(0);
            $table->decimal('read_completion_rate', 5, 4)->default(0);
            $table->decimal('retention_rate', 5, 4)->default(0);
            $table->unsignedInteger('avg_session_duration_seconds')->default(0);
            $table->decimal('recommendation_accuracy', 5, 4)->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();

            $table->unique(['date', 'feed_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_analytics_snapshots');
        Schema::dropIfExists('user_segment_memberships');
        Schema::dropIfExists('recommendation_logs');
        Schema::dropIfExists('user_bookmarks');
        Schema::dropIfExists('user_topic_scores');
        Schema::dropIfExists('user_source_scores');
        Schema::dropIfExists('user_category_scores');
        Schema::dropIfExists('user_interest_profiles');
        Schema::dropIfExists('user_behavior_events');
    }
};

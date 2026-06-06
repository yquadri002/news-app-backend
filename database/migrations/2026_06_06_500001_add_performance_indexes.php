<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->index(['status', 'published_at', 'trending_score'], 'articles_feed_index');
            $table->index(['is_breaking', 'breaking_score'], 'articles_breaking_index');
            $table->index(['category_id', 'published_at'], 'articles_category_published_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['platform', 'last_active_at'], 'users_platform_active_index');
            $table->index(['language', 'last_active_at'], 'users_language_active_index');
        });

        Schema::table('user_behavior_events', function (Blueprint $table) {
            $table->index(['user_id', 'event_type', 'occurred_at'], 'behavior_user_type_date_index');
        });

        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->index(['notification_id', 'status'], 'deliveries_notification_status_index');
            $table->index(['user_id', 'opened_at'], 'deliveries_user_opened_index');
        });

        Schema::table('revenue_events', function (Blueprint $table) {
            $table->index(['occurred_at', 'event_type', 'amount'], 'revenue_events_analytics_index');
        });

        Schema::table('article_views', function (Blueprint $table) {
            $table->index(['viewed_at', 'article_id'], 'article_views_date_article_index');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('articles_feed_index');
            $table->dropIndex('articles_breaking_index');
            $table->dropIndex('articles_category_published_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_platform_active_index');
            $table->dropIndex('users_language_active_index');
        });

        Schema::table('user_behavior_events', function (Blueprint $table) {
            $table->dropIndex('behavior_user_type_date_index');
        });

        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->dropIndex('deliveries_notification_status_index');
            $table->dropIndex('deliveries_user_opened_index');
        });

        Schema::table('revenue_events', function (Blueprint $table) {
            $table->dropIndex('revenue_events_analytics_index');
        });

        Schema::table('article_views', function (Blueprint $table) {
            $table->dropIndex('article_views_date_article_index');
        });
    }
};

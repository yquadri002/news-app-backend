<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('notification_type')->default('manual')->after('target_type');
            $table->string('digest_type')->nullable()->after('notification_type');
            $table->foreignId('article_id')->nullable()->after('digest_type')->constrained()->nullOnDelete();
            $table->string('ab_test_variant')->nullable()->after('article_id');
            $table->unsignedBigInteger('ab_test_id')->nullable()->after('ab_test_variant');
            $table->decimal('urgency_score', 8, 4)->nullable()->after('ab_test_id');
        });

        Schema::create('notification_user_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('timezone')->default('UTC');
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->unsignedTinyInteger('daily_cap')->default(5);
            $table->unsignedTinyInteger('daily_sent_count')->default(0);
            $table->date('daily_count_reset_date')->nullable();
            $table->timestamp('last_notification_at')->nullable();
            $table->timestamp('cooldown_until')->nullable();
            $table->decimal('sensitivity_score', 5, 4)->default(0.5);
            $table->unsignedInteger('total_received')->default(0);
            $table->unsignedInteger('total_opened')->default(0);
            $table->timestamps();
        });

        Schema::create('notification_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->decimal('relevance_score', 8, 4)->default(0);
            $table->decimal('urgency_score', 8, 4)->default(0);
            $table->decimal('combined_score', 8, 4)->default(0);
            $table->timestamp('optimal_send_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->foreignId('notification_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'combined_score']);
            $table->index(['optimal_send_at', 'status']);
            $table->index(['user_id', 'article_id']);
        });

        Schema::create('notification_digests', function (Blueprint $table) {
            $table->id();
            $table->string('digest_type');
            $table->date('digest_date');
            $table->string('status')->default('pending');
            $table->json('article_ids')->nullable();
            $table->unsignedInteger('target_user_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('notification_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['digest_type', 'digest_date']);
        });

        Schema::create('notification_ab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('test_type');
            $table->boolean('is_active')->default(true);
            $table->json('variants');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->string('winning_variant')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('notification_type')->nullable();
            $table->decimal('delivery_rate', 5, 4)->default(0);
            $table->decimal('open_rate', 5, 4)->default(0);
            $table->decimal('ctr', 5, 4)->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->decimal('retention_impact', 5, 4)->default(0);
            $table->unsignedBigInteger('total_sent')->default(0);
            $table->unsignedBigInteger('total_delivered')->default(0);
            $table->unsignedBigInteger('total_opened')->default(0);
            $table->unsignedBigInteger('total_clicked')->default(0);
            $table->timestamps();

            $table->unique(['date', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_analytics_snapshots');
        Schema::dropIfExists('notification_ab_tests');
        Schema::dropIfExists('notification_digests');
        Schema::dropIfExists('notification_recommendations');
        Schema::dropIfExists('notification_user_states');
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropColumn([
                'notification_type', 'digest_type', 'article_id',
                'ab_test_variant', 'ab_test_id', 'urgency_score',
            ]);
        });
    }
};

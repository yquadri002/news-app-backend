<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('read_count')->default(0);
            $table->unsignedBigInteger('share_count')->default(0);
            $table->unsignedBigInteger('views_1h')->default(0);
            $table->unsignedBigInteger('views_24h')->default(0);
            $table->decimal('trending_score', 10, 4)->default(0);
            $table->decimal('velocity_score', 10, 4)->default(0);
            $table->decimal('engagement_score', 10, 4)->default(0);
            $table->decimal('breaking_score', 8, 4)->default(0);
            $table->decimal('recency_score', 8, 4)->default(0);
            $table->unsignedInteger('source_confirmation_count')->default(1);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index('trending_score');
            $table->index('velocity_score');
            $table->index('breaking_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_metrics');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_source_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->unsignedInteger('items_fetched')->default(0);
            $table->unsignedInteger('items_processed')->default(0);
            $table->unsignedInteger('items_skipped')->default(0);
            $table->unsignedInteger('items_duplicates')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['rss_source_id', 'started_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_fetch_logs');
    }
};

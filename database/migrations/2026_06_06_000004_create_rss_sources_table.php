<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('url')->unique();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('health_status')->default('unknown');
            $table->unsignedInteger('error_count')->default(0);
            $table->text('last_error')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->unsignedInteger('fetch_interval_minutes')->default(30);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'priority']);
            $table->index('health_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_sources');
    }
};

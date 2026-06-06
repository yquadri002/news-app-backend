<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('device_id')->unique();
            $table->string('fcm_token')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('location')->nullable();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('last_active_at');
            $table->index('fcm_token');
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('interests')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('source_ids')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('location')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('breaking_news_enabled')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('user_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('criteria');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('user_segments');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('users');
    }
};

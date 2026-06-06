<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('placement_key')->unique();
            $table->string('format');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('frequency_cap')->default(0);
            $table->unsignedInteger('frequency_period_minutes')->default(60);
            $table->json('remote_config')->nullable();
            $table->string('ab_test_variant')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'placement_key']);
        });

        Schema::create('ad_ab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_placement_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('variant_key');
            $table->unsignedTinyInteger('traffic_percentage')->default(50);
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['ad_placement_id', 'variant_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_ab_tests');
        Schema::dropIfExists('ad_placements');
    }
};

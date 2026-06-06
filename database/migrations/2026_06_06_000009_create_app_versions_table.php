<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->unsignedInteger('version_code');
            $table->string('version_name');
            $table->boolean('is_force_update')->default(false);
            $table->boolean('is_soft_update')->default(false);
            $table->text('release_notes')->nullable();
            $table->string('download_url')->nullable();
            $table->unsignedInteger('min_supported_version_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'version_code']);
            $table->index(['platform', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};

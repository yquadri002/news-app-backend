<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('assignment_source')->default('auto');
            $table->decimal('confidence', 5, 4)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['article_id', 'category_id']);
            $table->index(['category_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_categories');
    }
};

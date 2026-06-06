<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('guid');
            $table->string('moderation_status')->default('pending')->after('status');
            $table->foreignId('auto_category_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('duplicate_of_id')->nullable()->after('auto_category_id')->constrained('articles')->nullOnDelete();
            $table->boolean('is_duplicate')->default(false)->after('duplicate_of_id');
            $table->string('canonical_url')->nullable()->after('external_url');
            $table->string('source_name')->nullable()->after('author');
            $table->string('title_hash', 64)->nullable()->after('title');
            $table->string('content_hash', 64)->nullable()->after('content');
            $table->decimal('breaking_score', 8, 4)->default(0)->after('is_breaking');
            $table->decimal('trending_score', 10, 4)->default(0)->after('breaking_score');
            $table->timestamp('processed_at')->nullable()->after('published_at');
            $table->text('rejection_reason')->nullable()->after('processed_at');

            $table->index('status');
            $table->index('moderation_status');
            $table->index('title_hash');
            $table->index('content_hash');
            $table->index(['trending_score', 'published_at']);
            $table->index(['breaking_score', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['auto_category_id']);
            $table->dropForeign(['duplicate_of_id']);
            $table->dropColumn([
                'status', 'moderation_status', 'auto_category_id', 'duplicate_of_id',
                'is_duplicate', 'canonical_url', 'source_name', 'title_hash',
                'content_hash', 'breaking_score', 'trending_score', 'processed_at', 'rejection_reason',
            ]);
        });
    }
};

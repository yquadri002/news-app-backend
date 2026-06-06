<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rss_source_id',
        'category_id',
        'auto_category_id',
        'title',
        'slug',
        'summary',
        'content',
        'image_url',
        'external_url',
        'canonical_url',
        'author',
        'source_name',
        'guid',
        'status',
        'moderation_status',
        'duplicate_of_id',
        'is_duplicate',
        'title_hash',
        'content_hash',
        'is_breaking',
        'breaking_score',
        'trending_score',
        'breaking_marked_at',
        'breaking_marked_by',
        'view_count',
        'published_at',
        'processed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'moderation_status' => ModerationStatus::class,
            'is_duplicate' => 'boolean',
            'is_breaking' => 'boolean',
            'breaking_score' => 'decimal:4',
            'trending_score' => 'decimal:4',
            'breaking_marked_at' => 'datetime',
            'published_at' => 'datetime',
            'processed_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function rssSource(): BelongsTo
    {
        return $this->belongsTo(RssSource::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function autoCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'auto_category_id');
    }

    public function breakingMarkedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'breaking_marked_by');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'duplicate_of_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(Article::class, 'duplicate_of_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ArticleTag::class);
    }

    public function metrics(): HasOne
    {
        return $this->hasOne(ArticleMetric::class);
    }

    public function assignedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_categories')
            ->withPivot(['assignment_source', 'confidence', 'is_primary'])
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', ArticleStatus::Approved)
            ->where('moderation_status', ModerationStatus::Approved)
            ->where('is_duplicate', false);
    }

    public function scopeBreaking($query)
    {
        return $query->where('is_breaking', true);
    }

    public function scopeTrending($query)
    {
        return $query->orderByDesc('trending_score');
    }
}

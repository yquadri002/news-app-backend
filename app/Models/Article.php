<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rss_source_id',
        'category_id',
        'title',
        'slug',
        'summary',
        'content',
        'image_url',
        'external_url',
        'author',
        'guid',
        'is_breaking',
        'breaking_marked_at',
        'breaking_marked_by',
        'view_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_breaking' => 'boolean',
            'breaking_marked_at' => 'datetime',
            'published_at' => 'datetime',
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

    public function breakingMarkedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'breaking_marked_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }
}

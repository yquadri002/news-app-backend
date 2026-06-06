<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleMetric extends Model
{
    protected $fillable = [
        'article_id',
        'read_count',
        'share_count',
        'views_1h',
        'views_24h',
        'trending_score',
        'velocity_score',
        'engagement_score',
        'breaking_score',
        'recency_score',
        'source_confirmation_count',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'trending_score' => 'decimal:4',
            'velocity_score' => 'decimal:4',
            'engagement_score' => 'decimal:4',
            'breaking_score' => 'decimal:4',
            'recency_score' => 'decimal:4',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

<?php

namespace App\Models;

use App\Enums\RecommendationFeedType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationLog extends Model
{
    protected $fillable = [
        'user_id',
        'feed_type',
        'article_id',
        'rank_score',
        'position',
        'was_clicked',
        'was_read',
        'read_time_seconds',
        'session_id',
        'score_breakdown',
        'served_at',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'feed_type' => RecommendationFeedType::class,
            'rank_score' => 'decimal:4',
            'was_clicked' => 'boolean',
            'was_read' => 'boolean',
            'score_breakdown' => 'array',
            'served_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

<?php

namespace App\Models;

use App\Enums\NotificationRecommendationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'article_id',
        'relevance_score',
        'urgency_score',
        'combined_score',
        'optimal_send_at',
        'status',
        'reason',
        'score_breakdown',
        'notification_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'relevance_score' => 'decimal:4',
            'urgency_score' => 'decimal:4',
            'combined_score' => 'decimal:4',
            'status' => NotificationRecommendationStatus::class,
            'score_breakdown' => 'array',
            'optimal_send_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}

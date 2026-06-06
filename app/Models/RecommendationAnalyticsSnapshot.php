<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationAnalyticsSnapshot extends Model
{
    protected $fillable = [
        'date',
        'feed_type',
        'ctr',
        'read_completion_rate',
        'retention_rate',
        'avg_session_duration_seconds',
        'recommendation_accuracy',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'ctr' => 'decimal:4',
            'read_completion_rate' => 'decimal:4',
            'retention_rate' => 'decimal:4',
            'recommendation_accuracy' => 'decimal:4',
        ];
    }
}

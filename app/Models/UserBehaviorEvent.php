<?php

namespace App\Models;

use App\Enums\BehaviorEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBehaviorEvent extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'event_type',
        'article_id',
        'category_id',
        'rss_source_id',
        'read_time_seconds',
        'scroll_depth_percent',
        'search_query',
        'session_id',
        'feed_type',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => BehaviorEventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
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

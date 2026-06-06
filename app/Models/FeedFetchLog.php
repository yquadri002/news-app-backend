<?php

namespace App\Models;

use App\Enums\FeedFetchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedFetchLog extends Model
{
    protected $fillable = [
        'rss_source_id',
        'status',
        'items_fetched',
        'items_processed',
        'items_skipped',
        'items_duplicates',
        'duration_ms',
        'retry_count',
        'error_message',
        'metadata',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FeedFetchStatus::class,
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function rssSource(): BelongsTo
    {
        return $this->belongsTo(RssSource::class);
    }
}

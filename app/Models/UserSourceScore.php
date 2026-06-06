<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSourceScore extends Model
{
    protected $fillable = [
        'user_id',
        'rss_source_id',
        'score',
        'explicit_score',
        'implicit_score',
        'interaction_count',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:4',
            'explicit_score' => 'decimal:4',
            'implicit_score' => 'decimal:4',
            'last_interaction_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rssSource(): BelongsTo
    {
        return $this->belongsTo(RssSource::class);
    }
}

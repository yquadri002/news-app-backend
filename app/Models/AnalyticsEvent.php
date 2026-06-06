<?php

namespace App\Models;

use App\Enums\AnalyticsEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type',
        'user_id',
        'device_id',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => AnalyticsEventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

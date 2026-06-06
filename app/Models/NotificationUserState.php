<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationUserState extends Model
{
    protected $fillable = [
        'user_id',
        'timezone',
        'quiet_hours_start',
        'quiet_hours_end',
        'daily_cap',
        'daily_sent_count',
        'daily_count_reset_date',
        'last_notification_at',
        'cooldown_until',
        'sensitivity_score',
        'total_received',
        'total_opened',
    ];

    protected function casts(): array
    {
        return [
            'daily_count_reset_date' => 'date',
            'last_notification_at' => 'datetime',
            'cooldown_until' => 'datetime',
            'sensitivity_score' => 'decimal:4',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

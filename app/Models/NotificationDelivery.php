<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'notification_id',
        'user_id',
        'fcm_token',
        'status',
        'delivered_at',
        'opened_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

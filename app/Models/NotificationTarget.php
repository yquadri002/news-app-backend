<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationTarget extends Model
{
    protected $fillable = [
        'notification_id',
        'targetable_type',
        'targetable_id',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }
}

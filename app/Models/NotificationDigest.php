<?php

namespace App\Models;

use App\Enums\DigestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDigest extends Model
{
    protected $fillable = [
        'digest_type',
        'digest_date',
        'status',
        'article_ids',
        'target_user_count',
        'sent_count',
        'scheduled_at',
        'sent_at',
        'notification_id',
    ];

    protected function casts(): array
    {
        return [
            'digest_type' => DigestType::class,
            'digest_date' => 'date',
            'article_ids' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}

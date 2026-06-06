<?php

namespace App\Models;

use App\Enums\DigestType;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Notification extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'body',
        'image_url',
        'action_type',
        'action_data',
        'target_type',
        'notification_type',
        'digest_type',
        'article_id',
        'ab_test_variant',
        'ab_test_id',
        'urgency_score',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'delivered_count',
        'opened_count',
        'failed_count',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'action_data' => 'array',
            'target_type' => NotificationTargetType::class,
            'notification_type' => NotificationType::class,
            'digest_type' => DigestType::class,
            'urgency_score' => 'decimal:4',
            'status' => NotificationStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(NotificationTarget::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}


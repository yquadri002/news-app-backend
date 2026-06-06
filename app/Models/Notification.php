<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
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
}

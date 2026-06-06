<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAnalyticsSnapshot extends Model
{
    protected $fillable = [
        'date',
        'notification_type',
        'delivery_rate',
        'open_rate',
        'ctr',
        'conversion_rate',
        'retention_impact',
        'total_sent',
        'total_delivered',
        'total_opened',
        'total_clicked',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'delivery_rate' => 'decimal:4',
            'open_rate' => 'decimal:4',
            'ctr' => 'decimal:4',
            'conversion_rate' => 'decimal:4',
            'retention_impact' => 'decimal:4',
        ];
    }
}

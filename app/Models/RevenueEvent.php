<?php

namespace App\Models;

use App\Enums\AdNetwork;
use App\Enums\RevenueEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'ad_network',
        'ad_placement_id',
        'category_id',
        'amount',
        'currency',
        'country',
        'platform',
        'ad_format',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => RevenueEventType::class,
            'ad_network' => AdNetwork::class,
            'amount' => 'decimal:6',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adPlacement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

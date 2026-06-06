<?php

namespace App\Models;

use App\Enums\AdNetwork;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdMediationWaterfall extends Model
{
    protected $fillable = [
        'ad_placement_id',
        'ad_network',
        'priority',
        'floor_price',
        'historical_ecpm',
        'fill_rate',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'ad_network' => AdNetwork::class,
            'floor_price' => 'decimal:4',
            'historical_ecpm' => 'decimal:4',
            'fill_rate' => 'decimal:4',
            'is_enabled' => 'boolean',
        ];
    }

    public function adPlacement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdAbTest extends Model
{
    protected $fillable = [
        'ad_placement_id',
        'name',
        'variant_key',
        'traffic_percentage',
        'config',
        'is_active',
        'impressions',
        'clicks',
        'revenue',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'revenue' => 'decimal:4',
        ];
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdPlacement::class, 'ad_placement_id');
    }
}

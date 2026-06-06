<?php

namespace App\Models;

use App\Enums\AdNetwork;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdRevenueSnapshot extends Model
{
    protected $fillable = [
        'date',
        'ad_network',
        'ad_placement_id',
        'country',
        'platform',
        'category_id',
        'impressions',
        'clicks',
        'requests',
        'revenue',
        'fill_rate',
        'ecpm',
        'ctr',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'ad_network' => AdNetwork::class,
            'revenue' => 'decimal:4',
            'fill_rate' => 'decimal:4',
            'ecpm' => 'decimal:4',
            'ctr' => 'decimal:4',
        ];
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

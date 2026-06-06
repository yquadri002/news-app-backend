<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    protected $fillable = [
        'name',
        'placement_key',
        'format',
        'is_enabled',
        'frequency_cap',
        'frequency_period_minutes',
        'remote_config',
        'ab_test_variant',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'remote_config' => 'array',
        ];
    }

    public function abTests(): HasMany
    {
        return $this->hasMany(AdAbTest::class);
    }

    public function mediationWaterfall(): HasMany
    {
        return $this->hasMany(AdMediationWaterfall::class)->orderBy('priority');
    }

    public function revenueEvents(): HasMany
    {
        return $this->hasMany(RevenueEvent::class);
    }
}

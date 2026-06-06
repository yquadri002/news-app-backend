<?php

namespace App\Models;

use App\Enums\AbTestStatus;
use App\Enums\RevenueAbTestType;
use Illuminate\Database\Eloquent\Model;

class AbTestResult extends Model
{
    protected $fillable = [
        'name',
        'test_type',
        'variants',
        'status',
        'winning_variant',
        'metrics',
        'impressions',
        'conversions',
        'revenue',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'test_type' => RevenueAbTestType::class,
            'status' => AbTestStatus::class,
            'variants' => 'array',
            'metrics' => 'array',
            'revenue' => 'decimal:4',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}

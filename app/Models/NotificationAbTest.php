<?php

namespace App\Models;

use App\Enums\AbTestType;
use Illuminate\Database\Eloquent\Model;

class NotificationAbTest extends Model
{
    protected $fillable = [
        'name',
        'test_type',
        'is_active',
        'variants',
        'impressions',
        'clicks',
        'conversions',
        'winning_variant',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'test_type' => AbTestType::class,
            'is_active' => 'boolean',
            'variants' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}

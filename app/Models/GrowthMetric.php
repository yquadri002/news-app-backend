<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrowthMetric extends Model
{
    protected $fillable = [
        'date',
        'dau',
        'wau',
        'mau',
        'new_users',
        'retention_d1',
        'retention_d7',
        'retention_d30',
        'avg_session_length',
        'avg_ltv',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'retention_d1' => 'decimal:4',
            'retention_d7' => 'decimal:4',
            'retention_d30' => 'decimal:4',
            'avg_session_length' => 'decimal:2',
            'avg_ltv' => 'decimal:4',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueRecord extends Model
{
    protected $fillable = [
        'source',
        'amount',
        'currency',
        'recorded_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'recorded_date' => 'date',
            'metadata' => 'array',
        ];
    }
}

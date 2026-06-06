<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRetentionSnapshot extends Model
{
    protected $fillable = [
        'cohort_date',
        'day_number',
        'cohort_size',
        'retained_users',
        'retention_rate',
    ];

    protected function casts(): array
    {
        return [
            'cohort_date' => 'date',
            'retention_rate' => 'decimal:2',
        ];
    }
}

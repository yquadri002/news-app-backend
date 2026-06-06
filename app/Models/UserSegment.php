<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSegment extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'criteria',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

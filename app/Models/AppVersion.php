<?php

namespace App\Models;

use App\Enums\AppPlatform;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'platform',
        'version_code',
        'version_name',
        'is_force_update',
        'is_soft_update',
        'release_notes',
        'download_url',
        'min_supported_version_code',
        'is_active',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => AppPlatform::class,
            'is_force_update' => 'boolean',
            'is_soft_update' => 'boolean',
            'is_active' => 'boolean',
            'released_at' => 'datetime',
        ];
    }
}

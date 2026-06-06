<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'interests',
        'category_ids',
        'source_ids',
        'language',
        'location',
        'notifications_enabled',
        'breaking_news_enabled',
    ];

    protected function casts(): array
    {
        return [
            'interests' => 'array',
            'category_ids' => 'array',
            'source_ids' => 'array',
            'notifications_enabled' => 'boolean',
            'breaking_news_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

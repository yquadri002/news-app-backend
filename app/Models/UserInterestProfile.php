<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserInterestProfile extends Model
{
    protected $fillable = [
        'user_id',
        'is_cold_start',
        'primary_segment',
        'top_topics',
        'profile_strength',
        'total_events',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_cold_start' => 'boolean',
            'top_topics' => 'array',
            'profile_strength' => 'decimal:4',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryScores(): HasMany
    {
        return $this->hasMany(UserCategoryScore::class, 'user_id', 'user_id');
    }
}

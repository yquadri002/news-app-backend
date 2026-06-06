<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTopicScore extends Model
{
    protected $fillable = [
        'user_id',
        'topic',
        'score',
        'interaction_count',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:4',
            'last_interaction_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

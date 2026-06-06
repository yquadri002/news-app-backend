<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSegmentMembership extends Model
{
    protected $fillable = [
        'user_id',
        'user_segment_id',
        'confidence',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:4',
            'assigned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(UserSegment::class, 'user_segment_id');
    }
}

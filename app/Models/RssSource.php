<?php

namespace App\Models;

use App\Enums\RssHealthStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RssSource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'url',
        'priority',
        'is_active',
        'health_status',
        'error_count',
        'last_error',
        'is_validated',
        'last_validated_at',
        'last_fetched_at',
        'fetch_interval_minutes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_validated' => 'boolean',
            'health_status' => RssHealthStatus::class,
            'last_validated_at' => 'datetime',
            'last_fetched_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}

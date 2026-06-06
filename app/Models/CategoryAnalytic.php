<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryAnalytic extends Model
{
    protected $fillable = [
        'category_id',
        'date',
        'view_count',
        'article_opens',
        'unique_users',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

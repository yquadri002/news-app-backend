<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleCategory extends Model
{
    protected $fillable = [
        'article_id',
        'category_id',
        'assignment_source',
        'confidence',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:4',
            'is_primary' => 'boolean',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

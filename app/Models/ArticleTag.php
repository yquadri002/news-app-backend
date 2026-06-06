<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTag extends Model
{
    protected $fillable = [
        'article_id',
        'tag',
        'source',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

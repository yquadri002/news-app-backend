<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'icon_url',
        'sort_order',
        'is_enabled',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function rssSources(): HasMany
    {
        return $this->hasMany(RssSource::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}

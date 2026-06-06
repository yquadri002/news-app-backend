<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }

    public function markBreaking(int $id, int $adminId): Article
    {
        $article = $this->findOrFail($id);
        $article->update([
            'is_breaking' => true,
            'breaking_marked_at' => now(),
            'breaking_marked_by' => $adminId,
        ]);

        return $article->fresh(['category', 'rssSource']);
    }

    public function incrementViewCount(int $id): void
    {
        $this->query()->where('id', $id)->increment('view_count');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_breaking'])) {
            $query->where('is_breaking', (bool) $filters['is_breaking']);
        }

        return $query->with(['category', 'rssSource'])->latest('published_at');
    }
}

<?php

namespace App\Repositories;

use App\Enums\ArticleStatus;
use App\Enums\ModerationStatus;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

    public function findByGuid(string $guid): ?Article
    {
        return $this->query()->where('guid', $guid)->first();
    }

    public function findByTitleHash(string $titleHash): Collection
    {
        return $this->query()
            ->where('title_hash', $titleHash)
            ->where('is_duplicate', false)
            ->get();
    }

    public function getFeed(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->publishedQuery()
            ->with(['category', 'rssSource', 'images', 'metrics']);

        if (! empty($filters['category_id'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('category_id', $filters['category_id'])
                    ->orWhere('auto_category_id', $filters['category_id'])
                    ->orWhereHas('assignedCategories', fn (Builder $c) => $c->where('categories.id', $filters['category_id']));
            });
        }

        if (! empty($filters['source_id'])) {
            $query->where('rss_source_id', $filters['source_id']);
        }

        return $query->latest('published_at')->paginate($perPage);
    }

    public function getTrending(int $limit = 20, ?int $categoryId = null): Collection
    {
        $query = $this->publishedQuery()
            ->with(['category', 'rssSource', 'metrics'])
            ->where('published_at', '>=', now()->subDays(3))
            ->orderByDesc('trending_score');

        if ($categoryId) {
            $query->where(function (Builder $q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                    ->orWhere('auto_category_id', $categoryId);
            });
        }

        return $query->limit($limit)->get();
    }

    public function getBreaking(int $limit = 10): Collection
    {
        return $this->publishedQuery()
            ->breaking()
            ->with(['category', 'rssSource', 'metrics'])
            ->orderByDesc('breaking_score')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function getLatest(int $limit = 20, ?int $categoryId = null): Collection
    {
        $query = $this->publishedQuery()
            ->with(['category', 'rssSource'])
            ->latest('published_at');

        if ($categoryId) {
            $query->where(function (Builder $q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                    ->orWhere('auto_category_id', $categoryId);
            });
        }

        return $query->limit($limit)->get();
    }

    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('summary', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['category', 'rssSource'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function getPendingModeration(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('moderation_status', ModerationStatus::Pending)
            ->where('is_duplicate', false)
            ->with(['category', 'rssSource', 'autoCategory', 'metrics'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function getDuplicates(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('is_duplicate', true)
            ->with(['duplicateOf', 'rssSource'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function approve(int $id): Article
    {
        $article = $this->findOrFail($id);
        $article->update([
            'status' => ArticleStatus::Approved,
            'moderation_status' => ModerationStatus::Approved,
        ]);

        return $article->fresh();
    }

    public function reject(int $id, string $reason): Article
    {
        $article = $this->findOrFail($id);
        $article->update([
            'status' => ArticleStatus::Rejected,
            'moderation_status' => ModerationStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        return $article->fresh();
    }

    public function getRecentForDuplicateCheck(int $hours = 48): Collection
    {
        return $this->query()
            ->where('created_at', '>=', now()->subHours($hours))
            ->where('is_duplicate', false)
            ->select(['id', 'title', 'title_hash', 'content_hash', 'rss_source_id', 'published_at'])
            ->get();
    }

    protected function publishedQuery(): Builder
    {
        return $this->query()->published();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['moderation_status'])) {
            $query->where('moderation_status', $filters['moderation_status']);
        }

        if (isset($filters['is_breaking'])) {
            $query->where('is_breaking', (bool) $filters['is_breaking']);
        }

        if (isset($filters['is_duplicate'])) {
            $query->where('is_duplicate', (bool) $filters['is_duplicate']);
        }

        return $query->with(['category', 'rssSource', 'metrics'])->latest('published_at');
    }
}

<?php

namespace App\Services;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NewsFeedService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function getFeed(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->articleRepository->getFeed($filters, $perPage);
    }

    public function getTrending(int $limit = 20, ?int $categoryId = null): Collection
    {
        return $this->articleRepository->getTrending($limit, $categoryId);
    }

    public function getBreaking(int $limit = 10): Collection
    {
        return $this->articleRepository->getBreaking($limit);
    }

    public function getLatest(int $limit = 20, ?int $categoryId = null): Collection
    {
        return $this->articleRepository->getLatest($limit, $categoryId);
    }

    public function getByCategory(int $categoryId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->articleRepository->getFeed(['category_id' => $categoryId], $perPage);
    }

    public function getArticle(int $id)
    {
        return $this->articleRepository->findOrFail($id)
            ->load(['category', 'autoCategory', 'rssSource', 'images', 'tags', 'metrics', 'assignedCategories']);
    }

    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->articleRepository->search($query, $perPage);
    }
}

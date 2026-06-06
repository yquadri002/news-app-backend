<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface extends BaseRepositoryInterface
{
    public function markBreaking(int $id, int $adminId): Article;

    public function incrementViewCount(int $id): void;

    public function findByGuid(string $guid): ?Article;

    public function findByTitleHash(string $titleHash): Collection;

    public function getFeed(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function getTrending(int $limit = 20, ?int $categoryId = null): Collection;

    public function getBreaking(int $limit = 10): Collection;

    public function getLatest(int $limit = 20, ?int $categoryId = null): Collection;

    public function search(string $query, int $perPage = 20): LengthAwarePaginator;

    public function getPendingModeration(int $perPage = 15): LengthAwarePaginator;

    public function getDuplicates(int $perPage = 15): LengthAwarePaginator;

    public function approve(int $id): Article;

    public function reject(int $id, string $reason): Article;

    public function getRecentForDuplicateCheck(int $hours = 48): Collection;
}

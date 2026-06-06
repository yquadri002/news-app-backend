<?php

namespace App\Repositories\Contracts;

use App\Models\Article;

interface ArticleRepositoryInterface extends BaseRepositoryInterface
{
    public function markBreaking(int $id, int $adminId): Article;

    public function incrementViewCount(int $id): void;
}

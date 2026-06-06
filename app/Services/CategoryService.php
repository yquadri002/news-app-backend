<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->categoryRepository->paginate($perPage, $filters);
    }

    public function getEnabled()
    {
        return $this->categoryRepository->getEnabledOrdered();
    }

    public function create(array $data): Category
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    public function updateSortOrder(array $orderedIds): void
    {
        $this->categoryRepository->updateSortOrder($orderedIds);
    }

    public function toggle(int $id, bool $enabled): Category
    {
        return $this->categoryRepository->update($id, ['is_enabled' => $enabled]);
    }
}

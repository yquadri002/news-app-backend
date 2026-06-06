<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->list(
            $request->only(['search', 'is_enabled']),
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        $category = $this->categoryService->create($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = app(\App\Repositories\Contracts\CategoryRepositoryInterface::class)->findOrFail($id);
        $this->authorize('view', $category);

        return response()->json(['data' => new CategoryResource($category)]);
    }

    public function update(StoreCategoryRequest $request, int $id): JsonResponse
    {
        $category = app(\App\Repositories\Contracts\CategoryRepositoryInterface::class)->findOrFail($id);
        $this->authorize('update', $category);
        $category = $this->categoryService->update($id, $request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = app(\App\Repositories\Contracts\CategoryRepositoryInterface::class)->findOrFail($id);
        $this->authorize('delete', $category);
        $this->categoryService->delete($id);

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    public function sortOrder(Request $request): JsonResponse
    {
        $request->validate(['ordered_ids' => ['required', 'array'], 'ordered_ids.*' => ['integer', 'exists:categories,id']]);
        $this->categoryService->updateSortOrder($request->ordered_ids);

        return response()->json(['message' => 'Sort order updated successfully.']);
    }

    public function toggle(int $id, Request $request): JsonResponse
    {
        $request->validate(['is_enabled' => ['required', 'boolean']]);
        $category = $this->categoryService->toggle($id, $request->boolean('is_enabled'));

        return response()->json([
            'message' => 'Category status updated.',
            'data' => new CategoryResource($category),
        ]);
    }
}

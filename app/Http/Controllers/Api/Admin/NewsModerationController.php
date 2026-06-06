<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsArticleResource;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsModerationController extends Controller
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function pending(Request $request): JsonResponse
    {
        $articles = $this->articleRepository->getPendingModeration(
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => NewsArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $article = $this->articleRepository->approve($id);

        return response()->json([
            'message' => 'Article approved.',
            'data' => new NewsArticleResource($article->load(['category', 'metrics'])),
        ]);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $article = $this->articleRepository->reject($id, $request->reason);

        return response()->json([
            'message' => 'Article rejected.',
            'data' => new NewsArticleResource($article),
        ]);
    }

    public function duplicates(Request $request): JsonResponse
    {
        $articles = $this->articleRepository->getDuplicates(
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => NewsArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleRepository->paginate(
            (int) $request->get('per_page', 15),
            $request->only(['search', 'status', 'moderation_status', 'category_id', 'is_breaking', 'is_duplicate']),
        );

        return response()->json([
            'data' => NewsArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
            ],
        ]);
    }
}

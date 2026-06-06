<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Services\BreakingNewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BreakingNewsController extends Controller
{
    public function __construct(
        private readonly BreakingNewsService $breakingNewsService,
    ) {
    }

    public function markBreaking(int $articleId, Request $request): JsonResponse
    {
        $article = $this->breakingNewsService->markBreaking($articleId, $request->user());

        return response()->json([
            'message' => 'Article marked as breaking news.',
            'data' => new ArticleResource($article),
        ]);
    }

    public function pushToAll(int $articleId, Request $request): JsonResponse
    {
        $this->breakingNewsService->pushToAll($articleId, $request->user());

        return response()->json(['message' => 'Breaking news pushed to all users.']);
    }

    public function pushToCategories(int $articleId, Request $request): JsonResponse
    {
        $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $this->breakingNewsService->pushToCategories(
            $articleId,
            $request->category_ids,
            $request->user(),
        );

        return response()->json(['message' => 'Breaking news pushed to selected categories.']);
    }

    public function pushToSegments(int $articleId, Request $request): JsonResponse
    {
        $request->validate([
            'segment_ids' => ['required', 'array', 'min:1'],
            'segment_ids.*' => ['integer', 'exists:user_segments,id'],
        ]);

        $this->breakingNewsService->pushToSegments(
            $articleId,
            $request->segment_ids,
            $request->user(),
        );

        return response()->json(['message' => 'Breaking news pushed to selected segments.']);
    }
}

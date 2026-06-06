<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsArticleResource;
use App\Services\NewsFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(
        private readonly NewsFeedService $newsFeedService,
    ) {
    }

    public function feed(Request $request): JsonResponse
    {
        $articles = $this->newsFeedService->getFeed(
            $request->only(['category_id', 'source_id']),
            (int) $request->get('per_page', 20),
        );

        return response()->json([
            'data' => NewsArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function trending(Request $request): JsonResponse
    {
        $articles = $this->newsFeedService->getTrending(
            (int) $request->get('limit', 20),
            $request->integer('category_id') ?: null,
        );

        return response()->json(['data' => NewsArticleResource::collection($articles)]);
    }

    public function breaking(Request $request): JsonResponse
    {
        $articles = $this->newsFeedService->getBreaking((int) $request->get('limit', 10));

        return response()->json(['data' => NewsArticleResource::collection($articles)]);
    }

    public function latest(Request $request): JsonResponse
    {
        $articles = $this->newsFeedService->getLatest(
            (int) $request->get('limit', 20),
            $request->integer('category_id') ?: null,
        );

        return response()->json(['data' => NewsArticleResource::collection($articles)]);
    }

    public function byCategory(int $categoryId, Request $request): JsonResponse
    {
        $articles = $this->newsFeedService->getByCategory(
            $categoryId,
            (int) $request->get('per_page', 20),
        );

        return response()->json([
            'data' => NewsArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function article(int $id): JsonResponse
    {
        $article = $this->newsFeedService->getArticle($id);

        return response()->json(['data' => new NewsArticleResource($article)]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:200']]);

        $articles = $this->newsFeedService->search(
            $request->q,
            (int) $request->get('per_page', 20),
        );

        return response()->json([
            'data' => NewsArticleResource::collection($articles),
            'meta' => [
                'query' => $request->q,
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {
    }

    public function trackArticleView(Request $request): JsonResponse
    {
        $data = $request->validate([
            'article_id' => ['required', 'integer', 'exists:articles,id'],
            'session_id' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
        ]);

        $data['user_id'] = $request->user()?->id;
        $data['device_id'] = $request->user()?->device_id;

        $this->analyticsService->trackArticleView($data);

        return response()->json(['message' => 'Article view tracked.']);
    }

    public function trackSearch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:500'],
            'results_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['user_id'] = $request->user()?->id;
        $data['device_id'] = $request->user()?->device_id;

        $this->analyticsService->trackSearch($data);

        return response()->json(['message' => 'Search tracked.']);
    }

    public function trackCategoryView(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $this->analyticsService->trackCategoryView(
            $data['category_id'],
            $request->user()?->id,
            $request->user()?->device_id,
        );

        return response()->json(['message' => 'Category view tracked.']);
    }
}

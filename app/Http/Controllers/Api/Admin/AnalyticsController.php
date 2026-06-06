<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function overview(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboardService->getOverview([
                'from' => $request->get('from'),
                'to' => $request->get('to'),
            ]),
        ]);
    }

    public function categoryAnalytics(int $categoryId, Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getCategoryAnalytics($categoryId, [
                'from' => $request->get('from'),
                'to' => $request->get('to'),
            ]),
        ]);
    }

    public function searchTrends(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getSearchTrends([
                'from' => $request->get('from'),
                'to' => $request->get('to'),
            ], (int) $request->get('limit', 20)),
        ]);
    }

    public function retention(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getRetentionData([
                'from' => $request->get('from'),
                'to' => $request->get('to'),
            ]),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Recommendation\RecommendationAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationAnalyticsController extends Controller
{
    public function __construct(
        private readonly RecommendationAnalyticsService $analyticsService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getMetrics([
                'from' => $request->get('from'),
                'to' => $request->get('to'),
            ]),
        ]);
    }

    public function calculateSnapshot(): JsonResponse
    {
        $this->analyticsService->calculateDailySnapshot();

        return response()->json(['message' => 'Recommendation analytics snapshot calculated.']);
    }
}

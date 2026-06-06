<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRevenueAbTestRequest;
use App\Http\Resources\AbTestResultResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\UserSubscriptionResource;
use App\Services\Revenue\AdMediationService;
use App\Services\Revenue\AdOptimizationService;
use App\Services\Revenue\RevenueAbTestService;
use App\Services\Revenue\RevenueAnalyticsService;
use App\Services\Revenue\RevenueDashboardService;
use App\Services\Revenue\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function __construct(
        private readonly RevenueDashboardService $dashboardService,
        private readonly RevenueAnalyticsService $analyticsService,
        private readonly SubscriptionService $subscriptionService,
        private readonly RevenueAbTestService $abTestService,
        private readonly AdOptimizationService $optimizationService,
        private readonly AdMediationService $mediationService,
    ) {
    }

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboardService->getDashboard(
                $request->only(['from', 'to'])
            ),
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getAnalytics(
                $request->only(['from', 'to'])
            ),
        ]);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        if ($request->boolean('plans_only')) {
            return response()->json([
                'data' => [
                    'plans' => SubscriptionPlanResource::collection($this->subscriptionService->getPlans()),
                    'metrics' => $this->subscriptionService->getMetrics($request->only(['from', 'to'])),
                ],
            ]);
        }

        $subscriptions = $this->subscriptionService->getSubscriptions(
            $request->only(['status', 'user_id']),
            (int) $request->get('per_page', 20),
        );

        return response()->json([
            'data' => [
                'plans' => SubscriptionPlanResource::collection($this->subscriptionService->getPlans()),
                'metrics' => $this->subscriptionService->getMetrics($request->only(['from', 'to'])),
                'subscriptions' => UserSubscriptionResource::collection($subscriptions),
            ],
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    public function storeAbTest(StoreRevenueAbTestRequest $request): JsonResponse
    {
        $data = $request->validated();
        $test = $this->abTestService->createTest(
            $data['name'],
            \App\Enums\RevenueAbTestType::from($data['test_type']),
            $data['variants'],
        );

        return response()->json([
            'message' => 'Revenue A/B test created.',
            'data' => new AbTestResultResource($test),
        ], 201);
    }

    public function optimization(Request $request): JsonResponse
    {
        $placementId = $request->integer('placement_id') ?: null;

        return response()->json([
            'data' => [
                'recommendations' => $this->optimizationService->getOptimizationRecommendations(),
                'mediation' => $placementId
                    ? $this->mediationService->getWaterfallForPlacement(
                        \App\Models\AdPlacement::findOrFail($placementId)
                    )
                    : [],
            ],
        ]);
    }
}

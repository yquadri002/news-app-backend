<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\TrackRevenueEventRequest;
use App\Http\Resources\SubscriptionPlanResource;
use App\Services\Revenue\RevenueEventTrackingService;
use App\Services\Revenue\RevenueEventVerifier;
use App\Services\Revenue\SubscriptionReceiptValidator;
use App\Services\Revenue\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function __construct(
        private readonly RevenueEventTrackingService $trackingService,
        private readonly RevenueEventVerifier $eventVerifier,
        private readonly SubscriptionReceiptValidator $receiptValidator,
        private readonly SubscriptionService $subscriptionService,
    ) {
    }

    public function trackEvent(TrackRevenueEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $this->eventVerifier->assertAllowed($user, $data);
        $data['user_id'] = $user->id;
        $data['platform'] = $user->platform;
        $data['country'] = $user->location;

        match ($data['event_type']) {
            'impression' => $this->trackingService->trackImpression($data),
            'click' => $this->trackingService->trackClick($data),
        };

        return response()->json(['message' => 'Revenue event recorded.']);
    }

    public function plans(): JsonResponse
    {
        return response()->json([
            'data' => SubscriptionPlanResource::collection($this->subscriptionService->getPlans()),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'store_transaction_id' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $this->receiptValidator->validate(
            $user->platform ?? 'unknown',
            $request->string('store_transaction_id')->toString(),
            $request->integer('plan_id'),
        );

        $subscription = $this->subscriptionService->subscribe(
            $user,
            $request->integer('plan_id'),
            $request->only(['store_transaction_id', 'metadata']),
        );

        return response()->json([
            'message' => 'Subscription activated.',
            'data' => new \App\Http\Resources\UserSubscriptionResource($subscription),
        ], 201);
    }
}

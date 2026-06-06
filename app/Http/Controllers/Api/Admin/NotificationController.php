<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->list(
            $request->only(['status', 'target_type']),
            (int) $request->get('per_page', 15),
        );

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $notification = $this->notificationService->create(
            $request->validated(),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Notification created successfully.',
            'data' => new NotificationResource($notification),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $notification = app(\App\Repositories\Contracts\NotificationRepositoryInterface::class)
            ->findOrFail($id)
            ->load('creator');

        return response()->json(['data' => new NotificationResource($notification)]);
    }

    public function schedule(int $id, Request $request): JsonResponse
    {
        $request->validate(['scheduled_at' => ['required', 'date', 'after:now']]);
        $notification = $this->notificationService->schedule($id, $request->date('scheduled_at'));

        return response()->json([
            'message' => 'Notification scheduled.',
            'data' => new NotificationResource($notification),
        ]);
    }

    public function send(int $id): JsonResponse
    {
        $notification = $this->notificationService->sendNow($id);

        return response()->json([
            'message' => 'Notification dispatch started.',
            'data' => new NotificationResource($notification),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $notification = $this->notificationService->cancel($id);

        return response()->json([
            'message' => 'Notification cancelled.',
            'data' => new NotificationResource($notification),
        ]);
    }

    public function analytics(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->notificationService->getDeliveryAnalytics($id),
        ]);
    }
}

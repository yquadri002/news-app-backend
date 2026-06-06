<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Services\NotificationIntelligence\NotificationFatigueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationFatigueService $fatigueService,
    ) {
    }

    public function trackOpen(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => ['required', 'integer', 'exists:notifications,id'],
            'delivery_id' => ['nullable', 'integer', 'exists:notification_deliveries,id'],
        ]);

        $user = $request->user();

        $delivery = $request->filled('delivery_id')
            ? NotificationDelivery::where('id', $request->integer('delivery_id'))
                ->where('user_id', $user->id)
                ->firstOrFail()
            : NotificationDelivery::where('notification_id', $request->integer('notification_id'))
                ->where('user_id', $user->id)
                ->latest()
                ->firstOrFail();

        if (! $delivery->opened_at) {
            $delivery->update(['opened_at' => now()]);
            $delivery->notification?->increment('opened_count');
            $this->fatigueService->recordOpened($user);
        }

        return response()->json(['message' => 'Notification open recorded.']);
    }
}

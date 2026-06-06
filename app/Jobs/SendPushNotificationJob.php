<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Services\FcmService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $notificationId)
    {
        $this->onQueue('notifications');
    }

    public function handle(
        NotificationRepositoryInterface $notificationRepository,
        NotificationService $notificationService,
        FcmService $fcmService,
    ): void {
        $notification = $notificationRepository->findOrFail($this->notificationId);
        $users = $notificationService->resolveRecipients($notification);

        $notificationRepository->update($notification->id, [
            'total_recipients' => $users->count(),
        ]);

        $result = $fcmService->sendToUsers($notification, $users);

        $notificationRepository->updateDeliveryStats($notification, [
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
            'delivered_count' => $result['delivered'],
            'failed_count' => $result['failed'],
        ]);
    }
}

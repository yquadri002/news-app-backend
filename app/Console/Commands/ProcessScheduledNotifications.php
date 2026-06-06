<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessScheduledNotifications extends Command
{
    protected $signature = 'notifications:process-scheduled';

    protected $description = 'Dispatch due scheduled push notifications';

    public function handle(
        NotificationRepositoryInterface $notificationRepository,
        NotificationService $notificationService,
    ): int {
        $notifications = $notificationRepository->getScheduledDue();

        foreach ($notifications as $notification) {
            $notificationService->dispatch($notification);
            $this->info("Dispatched notification #{$notification->id}");
        }

        $this->info("Processed {$notifications->count()} scheduled notifications.");

        return self::SUCCESS;
    }
}

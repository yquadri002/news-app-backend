<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Jobs\SendPushNotificationJob;
use App\Models\Notification;
use App\Models\UserSegment;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly FcmService $fcmService,
    ) {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->notificationRepository->paginate($perPage, $filters);
    }

    public function create(array $data, int $adminId): Notification
    {
        $data['created_by'] = $adminId;
        $data['status'] = ! empty($data['scheduled_at'])
            ? NotificationStatus::Scheduled
            : NotificationStatus::Draft;

        $notification = $this->notificationRepository->create($data);

        if (! empty($data['target_ids'])) {
            $this->syncTargets($notification, $data['target_type'], $data['target_ids']);
        }

        return $notification->load(['creator', 'targets']);
    }

    public function schedule(int $id, \DateTimeInterface $scheduledAt): Notification
    {
        return $this->notificationRepository->update($id, [
            'scheduled_at' => $scheduledAt,
            'status' => NotificationStatus::Scheduled,
        ]);
    }

    public function dispatch(Notification $notification): void
    {
        $this->notificationRepository->update($notification->id, [
            'status' => NotificationStatus::Processing,
        ]);

        SendPushNotificationJob::dispatch($notification->id);
    }

    public function sendNow(int $id): Notification
    {
        $notification = $this->notificationRepository->findOrFail($id);
        $this->dispatch($notification);

        return $notification->fresh();
    }

    public function cancel(int $id): Notification
    {
        return $this->notificationRepository->update($id, [
            'status' => NotificationStatus::Cancelled,
        ]);
    }

    public function getDeliveryAnalytics(int $id): array
    {
        $notification = $this->notificationRepository->findOrFail($id);
        $notification->load('deliveries');

        $delivered = $notification->deliveries->where('status', 'delivered')->count();
        $failed = $notification->deliveries->where('status', 'failed')->count();
        $opened = $notification->deliveries->whereNotNull('opened_at')->count();
        $total = $notification->deliveries->count();

        return [
            'notification' => $notification,
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'opened' => $opened,
            'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
            'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0,
        ];
    }

    public function resolveRecipients(Notification $notification): \Illuminate\Database\Eloquent\Collection
    {
        return match ($notification->target_type) {
            NotificationTargetType::All => $this->userRepository->getUsersForNotification([]),
            NotificationTargetType::Categories => $this->userRepository->getUsersForNotification([
                'category_ids' => $notification->targets->pluck('targetable_id')->toArray(),
            ]),
            NotificationTargetType::Segments => $this->resolveSegmentUsers($notification),
            default => collect(),
        };
    }

    private function resolveSegmentUsers(Notification $notification): \Illuminate\Database\Eloquent\Collection
    {
        $segmentIds = $notification->targets->pluck('targetable_id');
        $segments = UserSegment::whereIn('id', $segmentIds)->get();
        $allUsers = collect();

        foreach ($segments as $segment) {
            $users = $this->userRepository->getUsersForNotification([
                'segment_criteria' => $segment->criteria,
            ]);
            $allUsers = $allUsers->merge($users);
        }

        return $allUsers->unique('id');
    }

    private function syncTargets(Notification $notification, string $targetType, array $targetIds): void
    {
        $modelClass = match ($targetType) {
            NotificationTargetType::Categories->value => \App\Models\Category::class,
            NotificationTargetType::Segments->value => UserSegment::class,
            default => null,
        };

        if (! $modelClass) {
            return;
        }

        foreach ($targetIds as $targetId) {
            $notification->targets()->create([
                'targetable_type' => $modelClass,
                'targetable_id' => $targetId,
            ]);
        }
    }
}

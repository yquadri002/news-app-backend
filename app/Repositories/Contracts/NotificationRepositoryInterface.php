<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function getScheduledDue(): Collection;

    public function updateDeliveryStats(Notification $notification, array $stats): void;
}

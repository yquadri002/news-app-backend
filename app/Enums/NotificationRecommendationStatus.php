<?php

namespace App\Enums;

enum NotificationRecommendationStatus: string
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Sent = 'sent';
    case Skipped = 'skipped';
    case Expired = 'expired';
}

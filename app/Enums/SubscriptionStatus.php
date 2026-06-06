<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Paused = 'paused';
}

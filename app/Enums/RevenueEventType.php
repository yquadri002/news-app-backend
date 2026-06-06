<?php

namespace App\Enums;

enum RevenueEventType: string
{
    case Impression = 'impression';
    case Click = 'click';
    case Subscription = 'subscription';
    case Purchase = 'purchase';
    case Renewal = 'renewal';
    case TrialStart = 'trial_start';
    case TrialConvert = 'trial_convert';
}

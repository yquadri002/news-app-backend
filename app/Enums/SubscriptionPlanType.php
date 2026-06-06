<?php

namespace App\Enums;

enum SubscriptionPlanType: string
{
    case Premium = 'premium';
    case AdFree = 'ad_free';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}

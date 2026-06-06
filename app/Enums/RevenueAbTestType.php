<?php

namespace App\Enums;

enum RevenueAbTestType: string
{
    case AdFrequency = 'ad_frequency';
    case AdPlacement = 'ad_placement';
    case SubscriptionPricing = 'subscription_pricing';
    case PremiumOffer = 'premium_offer';
}

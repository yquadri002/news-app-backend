<?php

namespace App\Enums;

enum MonetizationSegment: string
{
    case HighRevenue = 'high_revenue';
    case LowRevenue = 'low_revenue';
    case AdSensitive = 'ad_sensitive';
    case Premium = 'premium';
    case HeavyReader = 'heavy_reader';
    case CasualReader = 'casual_reader';

    public function label(): string
    {
        return match ($this) {
            self::HighRevenue => 'High Revenue Users',
            self::LowRevenue => 'Low Revenue Users',
            self::AdSensitive => 'Ad Sensitive Users',
            self::Premium => 'Premium Users',
            self::HeavyReader => 'Heavy Readers',
            self::CasualReader => 'Casual Readers',
        };
    }
}

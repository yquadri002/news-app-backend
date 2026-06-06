<?php

namespace App\Enums;

enum AdNetwork: string
{
    case AdMob = 'admob';
    case Meta = 'meta';
    case AppLovin = 'applovin';
    case Unity = 'unity';
    case Pangle = 'pangle';

    public function label(): string
    {
        return config("revenue.ad_networks.{$this->value}.name", $this->value);
    }

    public function defaultEcpm(): float
    {
        return (float) config("revenue.ad_networks.{$this->value}.default_ecpm", 1.0);
    }
}

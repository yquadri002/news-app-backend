<?php

namespace App\Enums;

enum RssHealthStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unhealthy = 'unhealthy';
    case Unknown = 'unknown';
}

<?php

namespace App\Enums;

enum NotificationType: string
{
    case Manual = 'manual';
    case Breaking = 'breaking';
    case Digest = 'digest';
    case Recommendation = 'recommendation';
    case Automated = 'automated';
}

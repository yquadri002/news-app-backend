<?php

namespace App\Enums;

enum NotificationTargetType: string
{
    case All = 'all';
    case Categories = 'categories';
    case Segments = 'segments';
    case Users = 'users';
}

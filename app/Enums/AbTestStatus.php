<?php

namespace App\Enums;

enum AbTestStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Paused = 'paused';
}

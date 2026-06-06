<?php

namespace App\Enums;

enum AbTestType: string
{
    case Title = 'title';
    case SendTime = 'send_time';
    case Segment = 'segment';
}

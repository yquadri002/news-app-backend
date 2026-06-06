<?php

namespace App\Enums;

enum DigestType: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';

    public function defaultSendHour(): int
    {
        return match ($this) {
            self::Morning => 8,
            self::Afternoon => 13,
            self::Evening => 19,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Morning Digest',
            self::Afternoon => 'Afternoon Digest',
            self::Evening => 'Evening Digest',
        };
    }
}

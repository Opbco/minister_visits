<?php

namespace App\Enum;

enum Cycle: int
{
    case FIRST = 1;
    case SECOND = 2;
    case FIRST_SECOND = 3;

    public function label(): string
    {
        return match($this) {
            self::FIRST => 'Premier Cycle',
            self::SECOND => 'Second Cycle',
            self::FIRST_SECOND => 'Premier et Second Cycle',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::FIRST => 'First Cycle',
            self::SECOND => 'Second Cycle',
            self::FIRST_SECOND => 'First and Second Cycle',
        };
    }
}
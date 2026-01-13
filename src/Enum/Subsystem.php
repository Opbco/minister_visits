<?php

namespace App\Enum;

enum Subsystem: string
{
    case ANGLOPHONE = 'anglophone';
    case FRANCOPHONE = 'francophone';

    public function label(): string
    {
        return match($this) {
            self::ANGLOPHONE => 'Anglophone',
            self::FRANCOPHONE => 'Francophone',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::ANGLOPHONE => 'Anglophone',
            self::FRANCOPHONE => 'Francophone',
        };
    }
}
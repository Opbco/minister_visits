<?php

namespace App\Enum;

enum StructureOrdre: string
{
    case PUBLIC = 'public';
    case PRIVE_CATHOLIQUE = 'prive_catholique';
    case PRIVE_ISLAMIQUE = 'prive_islamique';
    case PRIVE_PROTESTANT = 'prive_protestant';
    case PRIVE_LAIC = 'prive_laic';

    public function label(): string
    {
        return match($this) {
            self::PUBLIC => 'Public',
            self::PRIVE_CATHOLIQUE => 'Privé Catholique',
            self::PRIVE_ISLAMIQUE => 'Privé Islamique',
            self::PRIVE_PROTESTANT => 'Privé Protestant',
            self::PRIVE_LAIC => 'Privé Laïc',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::PUBLIC => 'Public',
            self::PRIVE_CATHOLIQUE => 'Private Catholic',
            self::PRIVE_ISLAMIQUE => 'Private Islamic',
            self::PRIVE_PROTESTANT => 'Private Protestant',
            self::PRIVE_LAIC => 'Private Secular',
        };
    }
}
<?php

namespace App\Enum;

enum StructureCategory: int
{
    case CENTRAUX = 1;
    case DECONCENTRES = 2;

    public function label(): string
    {
        return match($this) {
            self::CENTRAUX => 'Centraux',
            self::DECONCENTRES => 'Déconcentrés',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::CENTRAUX => 'Central',
            self::DECONCENTRES => 'Deconcentrated',
        };
    }
}


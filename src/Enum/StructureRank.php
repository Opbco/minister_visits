<?php

namespace App\Enum;

enum StructureRank: int
{
    case Direction = 5;
    case SousDirection = 6;
    case Service = 7;
    case Bureau = 8;
    case SG = 3;
    case Ministre = 1;
    case IG = 4;
    case SE = 2;

    public function label(): string
    {
        return match($this) {
            self::Ministre => 'Ministre',
            self::SE => 'Secrétariat d\'Etat',
            self::SG => 'Secrétariat Général',
            self::IG => 'Inspection Général',
            self::Direction => 'Direction',
            self::SousDirection => 'Sous-Direction',
            self::Service => 'Service',
            self::Bureau => 'Bureau',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::Ministre => 'Minister',
            self::SE => 'State Secretariat',
            self::SG => 'General Secretariat',
            self::IG => 'General Inspection',
            self::Direction => 'Directorate',
            self::SousDirection => 'Sub-Directorate',
            self::Service => 'Service',
            self::Bureau => 'Office',
        };
    }
}
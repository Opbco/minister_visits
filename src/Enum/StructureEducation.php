<?php

namespace App\Enum;

enum StructureEducation: string
{
    case GENERAL = 'general';
    case TECHNICAL = 'technical';
    case PROFESSIONAL = 'professional';
    case POLYVALENT = 'polyvalent';

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'Enseignement Général',
            self::TECHNICAL => 'Enseignement Technique',
            self::PROFESSIONAL => 'Enseignement Professionnel',
            self::POLYVALENT => 'Enseignement Polyvalent',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::GENERAL => 'General Education',
            self::TECHNICAL => 'Technical Education',
            self::PROFESSIONAL => 'Professional Education',
            self::POLYVALENT => 'Polyvalent Education',
        };
    }
}
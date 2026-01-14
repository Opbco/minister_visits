<?php

namespace App\Enum;

enum ReunionStatut: int
{
    case BROUILLON = 1;
    case ENATTENTEVALIDATION = 2;
    case VALIDEE = 3;
    case REFUSEE = 4;

    public function label(): string
    {
        return match($this) {
            self::BROUILLON => 'Brouillon',
            self::ENATTENTEVALIDATION => 'En attente de validation',
            self::VALIDEE => 'Validée',
            self::REFUSEE => 'Refusée',
        };
    }

    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case;
        }
        return $choices;
    }

    public function labelEn(): string
    {
        return match($this) {
            self::BROUILLON => 'Draft',
            self::ENATTENTEVALIDATION => 'Pending approval',
            self::VALIDEE => 'Approved',
            self::REFUSEE => 'Rejected',
        };
    }
}
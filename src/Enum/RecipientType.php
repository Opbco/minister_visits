<?php

namespace App\Enum;

enum RecipientType: string
{
    case INTERNAL = 'internal';     // Internal staff (Personnel entity)
    case EXTERNAL = 'external';     // External participants (email/phone only)

    public function label(): string
    {
        return match($this) {
            self::INTERNAL => 'Personnel Interne',
            self::EXTERNAL => 'Participant Externe',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::INTERNAL => 'Internal Personnel',
            self::EXTERNAL => 'External Participant',
        };
    }

    public function getDescriptionEn(): string
    {
        return match($this) {
            self::INTERNAL => 'Internal personnel with system account',
            self::EXTERNAL => 'External consultant or guest without system account',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::INTERNAL => 'Personnel interne avec compte système',
            self::EXTERNAL => 'Consultant externe ou invité sans compte système',
        };
    }
}
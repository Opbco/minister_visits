<?php

namespace App\Enum;

enum ReunionStatut: int
{
    case PLANNED = 1;
    case CONFIRMED = 2;
    case IN_PROGRESS = 3;
    case COMPLETED = 4;
    case CANCELLED = 5;
    case POSTPONED = 6;
    case ARCHIVED = 7;

    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Planifiée',
            self::CONFIRMED => 'Confirmée',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
            self::POSTPONED => 'Reportée',
            self::ARCHIVED => 'Archivée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PLANNED => 'blue',
            self::CONFIRMED => 'green',
            self::IN_PROGRESS => 'orange',
            self::COMPLETED => 'grey',
            self::CANCELLED => 'red',
            self::POSTPONED => 'yellow',
            self::ARCHIVED => 'black',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::PLANNED => 'Planned',
            self::CONFIRMED => 'Confirmed',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::POSTPONED => 'Postponed',
            self::ARCHIVED => 'Archived',
        };
    }
}
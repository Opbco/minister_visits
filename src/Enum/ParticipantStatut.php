<?php

namespace App\Enum;

enum ParticipantStatut: string
{
    case Invited = 'invited';
    case Confirmed = 'confirmed';
    case Attended = 'attended';
    case Absent = 'absent';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Invited => 'Invité',
            self::Confirmed => 'Confirmé',
            self::Attended => 'Présent',
            self::Absent => 'Absent',
            self::Excused => 'Excusé',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::Invited => 'Invited',
            self::Confirmed => 'Confirmed',
            self::Attended => 'Attended',
            self::Absent => 'Absent',
            self::Excused => 'Excused',
        };
    }
}
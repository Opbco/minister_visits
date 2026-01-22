<?php

// File: src/Enum/MeetingTypeEnum.php

namespace App\Enum;

enum MeetingTypeEnum: string
{
    case IN_PERSON = 'in_person';
    case VIRTUAL = 'virtual';
    case HYBRID = 'hybrid';

    public function label(): string
    {
        return match($this) {
            self::IN_PERSON => 'En Présentiel',
            self::VIRTUAL => 'Virtuelle (en ligne uniquement)',
            self::HYBRID => 'Hybride (En Présentiel + Virtuelle)',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::IN_PERSON => 'In-Person',
            self::VIRTUAL => 'Virtual (Online Only)',
            self::HYBRID => 'Hybrid (In-Person + Virtual)',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::IN_PERSON => '🏢',
            self::VIRTUAL => '💻',
            self::HYBRID => '🔗',
        };
    }

    public function requiresPhysicalLocation(): bool
    {
        return $this === self::IN_PERSON || $this === self::HYBRID;
    }

    public function requiresVideoLink(): bool
    {
        return $this === self::VIRTUAL || $this === self::HYBRID;
    }

    public function getDescriptionEn(): string
    {
        return match($this) {
            self::IN_PERSON => 'All participants must attend physically at the meeting location',
            self::VIRTUAL => 'All participants join remotely via video conference',
            self::HYBRID => 'Participants can attend either in-person or remotely',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::IN_PERSON => 'Tous les participants doivent être présents physiquement sur le lieu de la réunion',
            self::VIRTUAL => 'Tous les participants se joignent à distance via une vidéoconférence',
            self::HYBRID => 'Les participants peuvent assister soit en personne, soit à distance',
        };
    }
}
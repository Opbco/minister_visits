<?php

namespace App\Enum;

enum VideoConferencePlatform: string
{
    case ZOOM = 'zoom';
    case GOOGLE_MEET = 'google_meet';
    case MICROSOFT_TEAMS = 'microsoft_teams';
    case WEBEX = 'webex';
    case JITSI = 'jitsi';
    case SKYPE = 'skype';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::ZOOM => 'Zoom',
            self::GOOGLE_MEET => 'Google Meet',
            self::MICROSOFT_TEAMS => 'Microsoft Teams',
            self::WEBEX => 'Cisco Webex',
            self::JITSI => 'Jitsi Meet',
            self::SKYPE => 'Skype',
            self::OTHER => 'Other Platform',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::ZOOM => '📹',
            self::GOOGLE_MEET => '📱',
            self::MICROSOFT_TEAMS => '💼',
            self::WEBEX => '🌐',
            self::JITSI => '🔓',
            self::SKYPE => '💬',
            self::OTHER => '🎥',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ZOOM => '#2D8CFF',
            self::GOOGLE_MEET => '#00897B',
            self::MICROSOFT_TEAMS => '#6264A7',
            self::WEBEX => '#00BCB4',
            self::JITSI => '#1D76BA',
            self::SKYPE => '#00AFF0',
            self::OTHER => '#6c757d',
        };
    }
}
<?php

// ==================== NOTIFICATION TYPE ENUM ====================
// File: src/Enum/NotificationType.php

namespace App\Enum;

enum NotificationType: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';           // Push notification (mobile app)
    case IN_APP = 'in_app';       // In-app notification (web interface)
    case WHATSAPP = 'whatsapp';   // WhatsApp notification (if integrated)

    public function getLabel(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::PUSH => 'Push Notification',
            self::IN_APP => 'In-App Notification',
            self::WHATSAPP => 'WhatsApp',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::EMAIL => '📧',
            self::SMS => '💬',
            self::PUSH => '🔔',
            self::IN_APP => '🔵',
            self::WHATSAPP => '💚',
        };
    }

    /**
     * Check if this type requires phone number
     */
    public function requiresPhone(): bool
    {
        return in_array($this, [self::SMS, self::WHATSAPP]);
    }

    /**
     * Check if this type requires email
     */
    public function requiresEmail(): bool
    {
        return $this === self::EMAIL;
    }

    /**
     * Check if this type requires Personnel entity
     */
    public function requiresPersonnel(): bool
    {
        return in_array($this, [self::PUSH, self::IN_APP]);
    }
}
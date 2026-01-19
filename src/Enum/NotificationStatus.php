<?php

namespace App\Enum;

enum NotificationStatus: string
{
    case PENDING = 'pending';       // Created but not sent yet
    case QUEUED = 'queued';         // In queue waiting to be sent
    case SENDING = 'sending';       // Currently being sent
    case SENT = 'sent';             // Successfully sent to provider
    case DELIVERED = 'delivered';   // Confirmed delivered to recipient
    case READ = 'read';             // Recipient has read the notification
    case FAILED = 'failed';         // Failed to send
    case CANCELLED = 'cancelled';   // Cancelled before sending

    public function labelEn(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::QUEUED => 'Queued',
            self::SENDING => 'Sending',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::READ => 'Read',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::QUEUED => 'En file d\'attente',
            self::SENDING => 'Envoi en cours',
            self::SENT => 'Envoyé',
            self::DELIVERED => 'Distribué',
            self::READ => 'Lu',
            self::FAILED => 'Échec',
            self::CANCELLED => 'Annulé',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::QUEUED => 'blue',
            self::SENDING => 'yellow',
            self::SENT => 'green',
            self::DELIVERED => 'teal',
            self::READ => 'purple',
            self::FAILED => 'red',
            self::CANCELLED => 'orange',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::PENDING => '⏳',
            self::QUEUED => '📋',
            self::SENDING => '📤',
            self::SENT => '✅',
            self::DELIVERED => '📬',
            self::READ => '👁️',
            self::FAILED => '❌',
            self::CANCELLED => '🚫',
        };
    }

    /**
     * Check if status is final (no further updates expected)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::READ, self::FAILED, self::CANCELLED]);
    }

    /**
     * Check if status indicates success
     */
    public function isSuccess(): bool
    {
        return in_array($this, [self::SENT, self::DELIVERED, self::READ]);
    }

    /**
     * Check if status allows retry
     */
    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }
}
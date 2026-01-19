<?php
namespace App\Enum;

enum NotificationTemplate: string
{
    case MEETING_INVITATION = 'meeting_invitation';
    case MEETING_REMINDER = 'meeting_reminder';
    case MEETING_CANCELLED = 'meeting_cancelled';
    case MEETING_POSTPONED = 'meeting_postponed';
    case MEETING_UPDATED = 'meeting_updated';
    case MEETING_STARTED = 'meeting_started';
    case MEETING_COMPLETED = 'meeting_completed';
    case ACTION_ITEM_ASSIGNED = 'action_item_assigned';
    case ACTION_ITEM_DUE = 'action_item_due';
    case REPORT_AVAILABLE = 'report_available';

    public function label(): string
    {
        return match($this) {
            self::MEETING_INVITATION => 'Invitation à la réunion',
            self::MEETING_REMINDER => 'Rappel de réunion',
            self::MEETING_CANCELLED => 'Réunion annulée',
            self::MEETING_POSTPONED => 'Réunion reportée',
            self::MEETING_UPDATED => 'Réunion mise à jour',
            self::MEETING_STARTED => 'Réunion démarrée',
            self::MEETING_COMPLETED => 'Réunion terminée',
            self::ACTION_ITEM_ASSIGNED => 'Action assignée',
            self::ACTION_ITEM_DUE => 'Action due',
            self::REPORT_AVAILABLE => 'Rapport disponible',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::MEETING_INVITATION => 'Meeting Invitation',
            self::MEETING_REMINDER => 'Meeting Reminder',
            self::MEETING_CANCELLED => 'Meeting Cancelled',
            self::MEETING_POSTPONED => 'Meeting Postponed',
            self::MEETING_UPDATED => 'Meeting Updated',
            self::MEETING_STARTED => 'Meeting Started',
            self::MEETING_COMPLETED => 'Meeting Completed',
            self::ACTION_ITEM_ASSIGNED => 'Action Item Assigned',
            self::ACTION_ITEM_DUE => 'Action Item Due',
            self::REPORT_AVAILABLE => 'Report Available',
        };
    }

    /**
     * Get default email subject for this template
     */
    public function getDefaultSubjectEn(): string
    {
        return match($this) {
            self::MEETING_INVITATION => 'Invitation: {meeting_title}',
            self::MEETING_REMINDER => 'Reminder: {meeting_title} - {time_until}',
            self::MEETING_CANCELLED => 'Cancelled: {meeting_title}',
            self::MEETING_POSTPONED => 'Postponed: {meeting_title}',
            self::MEETING_UPDATED => 'Updated: {meeting_title}',
            self::MEETING_STARTED => 'Meeting Started: {meeting_title}',
            self::MEETING_COMPLETED => 'Meeting Completed: {meeting_title}',
            self::ACTION_ITEM_ASSIGNED => 'New Action Item: {action_title}',
            self::ACTION_ITEM_DUE => 'Action Item Due: {action_title}',
            self::REPORT_AVAILABLE => 'Report Available: {meeting_title}',
        };
    }

    public function getDefaultSubject(): string
    {
        return match($this) {
            self::MEETING_INVITATION => 'Invitation : {meeting_title}',
            self::MEETING_REMINDER => 'Rappel : {meeting_title} - {time_until}',
            self::MEETING_CANCELLED => 'Annulé : {meeting_title}',
            self::MEETING_POSTPONED => 'Reporté : {meeting_title}',
            self::MEETING_UPDATED => 'Mis à jour : {meeting_title}',
            self::MEETING_STARTED => 'Réunion démarrée : {meeting_title}',
            self::MEETING_COMPLETED => 'Réunion terminée : {meeting_title}',
            self::ACTION_ITEM_ASSIGNED => 'Nouvelle action : {action_title}',
            self::ACTION_ITEM_DUE => 'Action due : {action_title}',
            self::REPORT_AVAILABLE => 'Rapport disponible : {meeting_title}',
        };
    }

    /**
     * Get default SMS message template
     */
    public function getDefaultSmsTemplateEn(): string
    {
        return match($this) {
            self::MEETING_INVITATION => 'You are invited to: {meeting_title} on {date} at {time}. Location: {location}',
            self::MEETING_REMINDER => 'Reminder: {meeting_title} starts in {time_until}. Location: {location}',
            self::MEETING_CANCELLED => '{meeting_title} on {date} has been cancelled. Reason: {reason}',
            self::MEETING_POSTPONED => '{meeting_title} postponed to {new_date} at {new_time}. Reason: {reason}',
            self::MEETING_UPDATED => '{meeting_title} has been updated. Check details.',
            self::MEETING_STARTED => '{meeting_title} is starting now at {location}',
            self::MEETING_COMPLETED => '{meeting_title} completed. Report will be available soon.',
            self::ACTION_ITEM_ASSIGNED => 'Action assigned: {action_title}. Due: {due_date}',
            self::ACTION_ITEM_DUE => 'Action due today: {action_title}',
            self::REPORT_AVAILABLE => 'Report for {meeting_title} is now available',
        };
    }

    public function getDefaultSmsTemplate(): string
    {
        return match($this) {
            self::MEETING_INVITATION => 'Vous êtes invité à : {meeting_title} le {date} à {time}. Lieu : {location}',
            self::MEETING_REMINDER => 'Rappel : {meeting_title} commence dans {time_until}. Lieu : {location}',
            self::MEETING_CANCELLED => '{meeting_title} du {date} a été annulée. Raison : {reason}',
            self::MEETING_POSTPONED => '{meeting_title} reportée au {new_date} à {new_time}. Raison : {reason}',
            self::MEETING_UPDATED => '{meeting_title} a été mise à jour. Vérifiez les détails.',
            self::MEETING_STARTED => '{meeting_title} commence maintenant à {location}',
            self::MEETING_COMPLETED => '{meeting_title} terminée. Le rapport sera disponible bientôt.',
            self::ACTION_ITEM_ASSIGNED => 'Action assignée : {action_title}. Échéance : {due_date}',
            self::ACTION_ITEM_DUE => "Action due aujourd'hui : {action_title}",
            self::REPORT_AVAILABLE => 'Le rapport pour {meeting_title} est maintenant disponible',
        };
    }

    /**
     * Determine when to send this notification relative to meeting time
     */
    public function getDefaultTiming(): ?string
    {
        return match($this) {
            self::MEETING_INVITATION => 'immediately',
            self::MEETING_REMINDER => '1_day_before', // or 1_hour_before
            self::MEETING_STARTED => 'at_start_time',
            default => 'immediately',
        };
    }
}
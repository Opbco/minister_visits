<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122143622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reunion ADD meeting_type VARCHAR(20) NOT NULL, ADD video_conference_platform VARCHAR(50) DEFAULT NULL, ADD video_conference_link VARCHAR(500) DEFAULT NULL, ADD video_conference_meeting_id VARCHAR(100) DEFAULT NULL, ADD video_conference_password VARCHAR(100) DEFAULT NULL, ADD video_conference_instructions LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reunion DROP meeting_type, DROP video_conference_platform, DROP video_conference_link, DROP video_conference_meeting_id, DROP video_conference_password, DROP video_conference_instructions');
    }
}

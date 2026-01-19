<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119071419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action_item (id INT AUTO_INCREMENT NOT NULL, reunion_id INT NOT NULL, responsable_id INT DEFAULT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, description LONGTEXT NOT NULL, date_echeance DATE DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, statut VARCHAR(20) NOT NULL, INDEX IDX_69FA9E104E9B7368 (reunion_id), INDEX IDX_69FA9E1053C59D72 (responsable_id), INDEX IDX_69FA9E10F987D8A8 (user_created_id), INDEX IDX_69FA9E10316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agenda_item (id INT AUTO_INCREMENT NOT NULL, reunion_id INT NOT NULL, presentateur_id INT DEFAULT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, ordre INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, duree_estimee INT DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_223E876E4E9B7368 (reunion_id), INDEX IDX_223E876EEC3FD9F4 (presentateur_id), INDEX IDX_223E876EF987D8A8 (user_created_id), INDEX IDX_223E876E316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE external_participant (id INT AUTO_INCREMENT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, organisation VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_BF582666F987D8A8 (user_created_id), INDEX IDX_BF582666316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_room (id INT AUTO_INCREMENT NOT NULL, structure_id INT DEFAULT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, capacite INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, equipements JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_9E6EA9492534008B (structure_id), INDEX IDX_9E6EA949F987D8A8 (user_created_id), INDEX IDX_9E6EA949316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, reunion_id INT NOT NULL, personnel_id INT DEFAULT NULL, external_participant_id INT DEFAULT NULL, user_created_id INT NOT NULL, type VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, subject VARCHAR(500) DEFAULT NULL, message LONGTEXT DEFAULT NULL, recipient_email VARCHAR(255) DEFAULT NULL, recipient_phone VARCHAR(50) DEFAULT NULL, recipient_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL, read_at DATETIME DEFAULT NULL, delivered_at DATETIME DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, retry_count INT DEFAULT NULL, last_retry_at DATETIME DEFAULT NULL, metadata JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_BF5476CAF987D8A8 (user_created_id), INDEX idx_notification_status (status), INDEX idx_notification_type (type), INDEX idx_notification_sent_at (sent_at), INDEX idx_notification_reunion (reunion_id), INDEX idx_notification_personnel (personnel_id), INDEX idx_notification_external (external_participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reunion_participation (id INT AUTO_INCREMENT NOT NULL, reunion_id INT NOT NULL, personnel_id INT DEFAULT NULL, external_participant_id INT DEFAULT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, confirmed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', absence_reason VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_EE2AD73A4E9B7368 (reunion_id), INDEX IDX_EE2AD73A1C109075 (personnel_id), INDEX IDX_EE2AD73A8C7528FF (external_participant_id), INDEX IDX_EE2AD73AF987D8A8 (user_created_id), INDEX IDX_EE2AD73A316B011F (user_updated_id), INDEX idx_participation_status (status), UNIQUE INDEX unique_reunion_personnel (reunion_id, personnel_id), UNIQUE INDEX unique_reunion_external (reunion_id, external_participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE action_item ADD CONSTRAINT FK_69FA9E104E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('ALTER TABLE action_item ADD CONSTRAINT FK_69FA9E1053C59D72 FOREIGN KEY (responsable_id) REFERENCES personnel (id)');
        $this->addSql('ALTER TABLE action_item ADD CONSTRAINT FK_69FA9E10F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE action_item ADD CONSTRAINT FK_69FA9E10316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE agenda_item ADD CONSTRAINT FK_223E876E4E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('ALTER TABLE agenda_item ADD CONSTRAINT FK_223E876EEC3FD9F4 FOREIGN KEY (presentateur_id) REFERENCES reunion_participation (id)');
        $this->addSql('ALTER TABLE agenda_item ADD CONSTRAINT FK_223E876EF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE agenda_item ADD CONSTRAINT FK_223E876E316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE external_participant ADD CONSTRAINT FK_BF582666F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE external_participant ADD CONSTRAINT FK_BF582666316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE meeting_room ADD CONSTRAINT FK_9E6EA9492534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE meeting_room ADD CONSTRAINT FK_9E6EA949F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE meeting_room ADD CONSTRAINT FK_9E6EA949316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA1C109075 FOREIGN KEY (personnel_id) REFERENCES personnel (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA8C7528FF FOREIGN KEY (external_participant_id) REFERENCES external_participant (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion_participation ADD CONSTRAINT FK_EE2AD73A4E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('ALTER TABLE reunion_participation ADD CONSTRAINT FK_EE2AD73A1C109075 FOREIGN KEY (personnel_id) REFERENCES personnel (id)');
        $this->addSql('ALTER TABLE reunion_participation ADD CONSTRAINT FK_EE2AD73A8C7528FF FOREIGN KEY (external_participant_id) REFERENCES external_participant (id)');
        $this->addSql('ALTER TABLE reunion_participation ADD CONSTRAINT FK_EE2AD73AF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion_participation ADD CONSTRAINT FK_EE2AD73A316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion_personnel DROP FOREIGN KEY FK_914AE5521C109075');
        $this->addSql('ALTER TABLE reunion_personnel DROP FOREIGN KEY FK_914AE5524E9B7368');
        $this->addSql('DROP TABLE reunion_personnel');
        $this->addSql('ALTER TABLE document ADD file_size BIGINT DEFAULT NULL, ADD original_file_name VARCHAR(255) DEFAULT NULL, CHANGE file_name file_name VARCHAR(100) DEFAULT NULL, CHANGE mime_type mime_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE reunion ADD salle_id INT DEFAULT NULL, ADD nouvelle_date_debut DATETIME DEFAULT NULL, ADD date_validation DATETIME DEFAULT NULL, DROP motif_rejet, CHANGE participants_externes motif_report LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482DC304035 FOREIGN KEY (salle_id) REFERENCES meeting_room (id)');
        $this->addSql('CREATE INDEX IDX_5B00A482DC304035 ON reunion (salle_id)');
        $this->addSql('ALTER TABLE reunion RENAME INDEX idx_5b00a482b40a33c7 TO idx_reunion_president');
        $this->addSql('ALTER TABLE reunion RENAME INDEX idx_5b00a482d936b2fa TO idx_reunion_organisateur');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482DC304035');
        $this->addSql('CREATE TABLE reunion_personnel (reunion_id INT NOT NULL, personnel_id INT NOT NULL, INDEX IDX_914AE5524E9B7368 (reunion_id), INDEX IDX_914AE5521C109075 (personnel_id), PRIMARY KEY(reunion_id, personnel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reunion_personnel ADD CONSTRAINT FK_914AE5521C109075 FOREIGN KEY (personnel_id) REFERENCES personnel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reunion_personnel ADD CONSTRAINT FK_914AE5524E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_item DROP FOREIGN KEY FK_69FA9E104E9B7368');
        $this->addSql('ALTER TABLE action_item DROP FOREIGN KEY FK_69FA9E1053C59D72');
        $this->addSql('ALTER TABLE action_item DROP FOREIGN KEY FK_69FA9E10F987D8A8');
        $this->addSql('ALTER TABLE action_item DROP FOREIGN KEY FK_69FA9E10316B011F');
        $this->addSql('ALTER TABLE agenda_item DROP FOREIGN KEY FK_223E876E4E9B7368');
        $this->addSql('ALTER TABLE agenda_item DROP FOREIGN KEY FK_223E876EEC3FD9F4');
        $this->addSql('ALTER TABLE agenda_item DROP FOREIGN KEY FK_223E876EF987D8A8');
        $this->addSql('ALTER TABLE agenda_item DROP FOREIGN KEY FK_223E876E316B011F');
        $this->addSql('ALTER TABLE external_participant DROP FOREIGN KEY FK_BF582666F987D8A8');
        $this->addSql('ALTER TABLE external_participant DROP FOREIGN KEY FK_BF582666316B011F');
        $this->addSql('ALTER TABLE meeting_room DROP FOREIGN KEY FK_9E6EA9492534008B');
        $this->addSql('ALTER TABLE meeting_room DROP FOREIGN KEY FK_9E6EA949F987D8A8');
        $this->addSql('ALTER TABLE meeting_room DROP FOREIGN KEY FK_9E6EA949316B011F');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA4E9B7368');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA1C109075');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA8C7528FF');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF987D8A8');
        $this->addSql('ALTER TABLE reunion_participation DROP FOREIGN KEY FK_EE2AD73A4E9B7368');
        $this->addSql('ALTER TABLE reunion_participation DROP FOREIGN KEY FK_EE2AD73A1C109075');
        $this->addSql('ALTER TABLE reunion_participation DROP FOREIGN KEY FK_EE2AD73A8C7528FF');
        $this->addSql('ALTER TABLE reunion_participation DROP FOREIGN KEY FK_EE2AD73AF987D8A8');
        $this->addSql('ALTER TABLE reunion_participation DROP FOREIGN KEY FK_EE2AD73A316B011F');
        $this->addSql('DROP TABLE action_item');
        $this->addSql('DROP TABLE agenda_item');
        $this->addSql('DROP TABLE external_participant');
        $this->addSql('DROP TABLE meeting_room');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE reunion_participation');
        $this->addSql('ALTER TABLE document DROP file_size, DROP original_file_name, CHANGE file_name file_name VARCHAR(100) NOT NULL, CHANGE mime_type mime_type VARCHAR(100) NOT NULL');
        $this->addSql('DROP INDEX IDX_5B00A482DC304035 ON reunion');
        $this->addSql('ALTER TABLE reunion ADD motif_rejet VARCHAR(255) DEFAULT NULL, DROP salle_id, DROP nouvelle_date_debut, DROP date_validation, CHANGE motif_report participants_externes LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reunion RENAME INDEX idx_reunion_president TO IDX_5B00A482B40A33C7');
        $this->addSql('ALTER TABLE reunion RENAME INDEX idx_reunion_organisateur TO IDX_5B00A482D936B2FA');
    }
}

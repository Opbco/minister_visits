<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114194217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE visite (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, structure_id INT NOT NULL, rapport_id INT DEFAULT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', date_arrivee DATETIME NOT NULL, date_depart DATETIME DEFAULT NULL, details LONGTEXT DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_B09C8CBBFD02F13 (evenement_id), INDEX IDX_B09C8CBB2534008B (structure_id), UNIQUE INDEX UNIQ_B09C8CBB1DFBCC46 (rapport_id), INDEX IDX_B09C8CBBF987D8A8 (user_created_id), INDEX IDX_B09C8CBB316B011F (user_updated_id), INDEX idx_visite_date_arrivee (date_arrivee), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBB2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBB1DFBCC46 FOREIGN KEY (rapport_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBB316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE document ADD visite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76C1C5DC59 FOREIGN KEY (visite_id) REFERENCES visite (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76C1C5DC59 ON document (visite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76C1C5DC59');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBFD02F13');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBB2534008B');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBB1DFBCC46');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBF987D8A8');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBB316B011F');
        $this->addSql('DROP TABLE visite');
        $this->addSql('DROP INDEX IDX_D8698A76C1C5DC59 ON document');
        $this->addSql('ALTER TABLE document DROP visite_id');
    }
}

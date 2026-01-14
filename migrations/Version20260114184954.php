<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114184954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, theme VARCHAR(255) DEFAULT NULL, objectifs LONGTEXT DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_B26681EF987D8A8 (user_created_id), INDEX IDX_B26681E316B011F (user_updated_id), INDEX idx_evenement_date_debut (date_debut), INDEX idx_evenement_libelle (libelle), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EF987D8A8');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E316B011F');
        $this->addSql('DROP TABLE evenement');
    }
}

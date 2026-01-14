<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114064135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE structure (id INT AUTO_INCREMENT NOT NULL, subdivision_id INT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, name_fr VARCHAR(255) NOT NULL, name_en VARCHAR(255) DEFAULT NULL, acronym VARCHAR(50) DEFAULT NULL, category INT NOT NULL, type VARCHAR(100) NOT NULL, level_rank INT DEFAULT NULL, education VARCHAR(100) DEFAULT NULL, ordre VARCHAR(100) DEFAULT NULL, cycle INT DEFAULT NULL, has_industrial TINYINT(1) DEFAULT 0 NOT NULL, has_commercial TINYINT(1) DEFAULT 0 NOT NULL, has_agricultural TINYINT(1) DEFAULT 0 NOT NULL, adress VARCHAR(255) DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, altitude NUMERIC(10, 7) DEFAULT NULL, subsystem VARCHAR(100) DEFAULT NULL, is_bilingual TINYINT(1) DEFAULT 0 NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_6F0137EAE05F13C (subdivision_id), INDEX IDX_6F0137EAF987D8A8 (user_created_id), INDEX IDX_6F0137EA316B011F (user_updated_id), INDEX IDX_6F0137EA727ACA70 (parent_id), INDEX idx_structure_name_fr (name_fr), INDEX idx_structure_name_en (name_en), INDEX idx_structure_acronym (acronym), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAE05F13C FOREIGN KEY (subdivision_id) REFERENCES sub_division (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA727ACA70 FOREIGN KEY (parent_id) REFERENCES structure (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EAE05F13C');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EAF987D8A8');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EA316B011F');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EA727ACA70');
        $this->addSql('DROP TABLE structure');
    }
}

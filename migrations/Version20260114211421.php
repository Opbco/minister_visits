<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114211421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fonction (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, abbreviation VARCHAR(50) DEFAULT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personnel (id INT AUTO_INCREMENT NOT NULL, fonction_id INT NOT NULL, structure_id INT NOT NULL, user_account_id INT DEFAULT NULL, nom_complet VARCHAR(255) NOT NULL, matricule VARCHAR(100) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, INDEX IDX_A6BCF3DE57889920 (fonction_id), INDEX IDX_A6BCF3DE2534008B (structure_id), UNIQUE INDEX UNIQ_A6BCF3DE3C0C9956 (user_account_id), INDEX idx_personnel_nom_prenom (nom_complet), INDEX idx_personnel_matricule (matricule), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reunion (id INT AUTO_INCREMENT NOT NULL, organisateur_id INT NOT NULL, president_id INT DEFAULT NULL, rapport_id INT DEFAULT NULL, user_validated_id INT DEFAULT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, objet VARCHAR(255) NOT NULL, type VARCHAR(100) DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, lieu VARCHAR(255) DEFAULT NULL, participants_externes LONGTEXT DEFAULT NULL, compte_rendu LONGTEXT DEFAULT NULL, statut VARCHAR(255) NOT NULL, motif_rejet VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_5B00A482D936B2FA (organisateur_id), INDEX IDX_5B00A482B40A33C7 (president_id), UNIQUE INDEX UNIQ_5B00A4821DFBCC46 (rapport_id), INDEX IDX_5B00A482F6A260C (user_validated_id), INDEX IDX_5B00A482F987D8A8 (user_created_id), INDEX IDX_5B00A482316B011F (user_updated_id), INDEX idx_reunion_date_debut (date_debut), INDEX idx_reunion_statut (statut), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reunion_personnel (reunion_id INT NOT NULL, personnel_id INT NOT NULL, INDEX IDX_914AE5524E9B7368 (reunion_id), INDEX IDX_914AE5521C109075 (personnel_id), PRIMARY KEY(reunion_id, personnel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DE57889920 FOREIGN KEY (fonction_id) REFERENCES fonction (id)');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DE2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DE3C0C9956 FOREIGN KEY (user_account_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482D936B2FA FOREIGN KEY (organisateur_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482B40A33C7 FOREIGN KEY (president_id) REFERENCES personnel (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A4821DFBCC46 FOREIGN KEY (rapport_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482F6A260C FOREIGN KEY (user_validated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE reunion_personnel ADD CONSTRAINT FK_914AE5524E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reunion_personnel ADD CONSTRAINT FK_914AE5521C109075 FOREIGN KEY (personnel_id) REFERENCES personnel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document ADD reunion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A764E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('CREATE INDEX IDX_D8698A764E9B7368 ON document (reunion_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A764E9B7368');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DE57889920');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DE2534008B');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DE3C0C9956');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482D936B2FA');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482B40A33C7');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A4821DFBCC46');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482F6A260C');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482F987D8A8');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482316B011F');
        $this->addSql('ALTER TABLE reunion_personnel DROP FOREIGN KEY FK_914AE5524E9B7368');
        $this->addSql('ALTER TABLE reunion_personnel DROP FOREIGN KEY FK_914AE5521C109075');
        $this->addSql('DROP TABLE fonction');
        $this->addSql('DROP TABLE personnel');
        $this->addSql('DROP TABLE reunion');
        $this->addSql('DROP TABLE reunion_personnel');
        $this->addSql('DROP INDEX IDX_D8698A764E9B7368 ON document');
        $this->addSql('ALTER TABLE document DROP reunion_id');
    }
}

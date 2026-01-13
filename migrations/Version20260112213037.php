<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112213037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_5373C966F987D8A8 (user_created_id), INDEX IDX_5373C966316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE division (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_101747145E237E06 (name), INDEX IDX_1017471498260155 (region_id), INDEX IDX_10174714F987D8A8 (user_created_id), INDEX IDX_10174714316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(100) NOT NULL, updated DATETIME DEFAULT NULL, mime_type VARCHAR(100) NOT NULL, context VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_D8698A76D7DF1668 (file_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F62F1765E237E06 (name), INDEX IDX_F62F176F987D8A8 (user_created_id), INDEX IDX_F62F176316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_division (id INT AUTO_INCREMENT NOT NULL, division_id INT NOT NULL, user_created_id INT NOT NULL, user_updated_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_AAB8A94D5E237E06 (name), INDEX IDX_AAB8A94D41859289 (division_id), INDEX IDX_AAB8A94DF987D8A8 (user_created_id), INDEX IDX_AAB8A94D316B011F (user_updated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_32745D0A92FC23A8 (username_canonical), UNIQUE INDEX UNIQ_32745D0AA0D96FBF (email_canonical), UNIQUE INDEX UNIQ_32745D0AC05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE country ADD CONSTRAINT FK_5373C966F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE country ADD CONSTRAINT FK_5373C966316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE division ADD CONSTRAINT FK_1017471498260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE division ADD CONSTRAINT FK_10174714F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE division ADD CONSTRAINT FK_10174714316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176F987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE sub_division ADD CONSTRAINT FK_AAB8A94D41859289 FOREIGN KEY (division_id) REFERENCES division (id)');
        $this->addSql('ALTER TABLE sub_division ADD CONSTRAINT FK_AAB8A94DF987D8A8 FOREIGN KEY (user_created_id) REFERENCES user__user (id)');
        $this->addSql('ALTER TABLE sub_division ADD CONSTRAINT FK_AAB8A94D316B011F FOREIGN KEY (user_updated_id) REFERENCES user__user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP FOREIGN KEY FK_5373C966F987D8A8');
        $this->addSql('ALTER TABLE country DROP FOREIGN KEY FK_5373C966316B011F');
        $this->addSql('ALTER TABLE division DROP FOREIGN KEY FK_1017471498260155');
        $this->addSql('ALTER TABLE division DROP FOREIGN KEY FK_10174714F987D8A8');
        $this->addSql('ALTER TABLE division DROP FOREIGN KEY FK_10174714316B011F');
        $this->addSql('ALTER TABLE region DROP FOREIGN KEY FK_F62F176F987D8A8');
        $this->addSql('ALTER TABLE region DROP FOREIGN KEY FK_F62F176316B011F');
        $this->addSql('ALTER TABLE sub_division DROP FOREIGN KEY FK_AAB8A94D41859289');
        $this->addSql('ALTER TABLE sub_division DROP FOREIGN KEY FK_AAB8A94DF987D8A8');
        $this->addSql('ALTER TABLE sub_division DROP FOREIGN KEY FK_AAB8A94D316B011F');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE division');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE sub_division');
        $this->addSql('DROP TABLE user__user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

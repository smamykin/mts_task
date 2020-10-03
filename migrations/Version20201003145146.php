<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201003145146 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vehicles (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, INDEX number_idx (number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visits (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, INDEX vehicle_idx (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE visits ADD CONSTRAINT FK_444839EA545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visits DROP FOREIGN KEY FK_444839EA545317D1');
        $this->addSql('DROP TABLE vehicles');
        $this->addSql('DROP TABLE visits');
    }
}

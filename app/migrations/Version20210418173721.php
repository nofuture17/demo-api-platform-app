<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210418173721 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE catalog_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalog_field_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalog_record_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalog (id INT NOT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE catalog_field (id INT NOT NULL, catalog_id INT NOT NULL, name VARCHAR(255) NOT NULL, key VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E2E624E1CC3C66FC ON catalog_field (catalog_id)');
        $this->addSql('CREATE TABLE catalog_record (id INT NOT NULL, catalog_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_293087D8CC3C66FC ON catalog_record (catalog_id)');
        $this->addSql('ALTER TABLE catalog_field ADD CONSTRAINT FK_E2E624E1CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_record ADD CONSTRAINT FK_293087D8CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE catalog_field DROP CONSTRAINT FK_E2E624E1CC3C66FC');
        $this->addSql('ALTER TABLE catalog_record DROP CONSTRAINT FK_293087D8CC3C66FC');
        $this->addSql('DROP SEQUENCE catalog_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalog_field_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalog_record_id_seq CASCADE');
        $this->addSql('DROP TABLE catalog');
        $this->addSql('DROP TABLE catalog_field');
        $this->addSql('DROP TABLE catalog_record');
    }
}

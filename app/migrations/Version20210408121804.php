<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210408121804 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image ADD height INT NOT NULL');
        $this->addSql('ALTER TABLE image ADD width INT NOT NULL');
        $this->addSql('ALTER TABLE image ADD extension VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE image ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image DROP height');
        $this->addSql('ALTER TABLE image DROP width');
        $this->addSql('ALTER TABLE image DROP extension');
        $this->addSql('ALTER TABLE image DROP created_at');
    }
}

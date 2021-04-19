<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Catalog;
use App\Entity\CatalogField;
use App\Entity\CatalogRecord;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class CatalogFixture extends Fixture
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function load(ObjectManager $manager): void
    {
        $catalog = (new Catalog())->setName('Тестовый каталог')
            ->setKey('test');
        $manager->persist($catalog);
        $manager->persist(
            (new CatalogField())->setName('Тестовое поле string')
                ->setKey('string')
                ->setType('string')
                ->setCatalog($catalog)
        );
        $manager->persist(
            (new CatalogField())->setName('Тестовое поле boolean')
                ->setKey('boolean')
                ->setType('boolean')
                ->setCatalog($catalog)
        );

        $record1 = (new CatalogRecord())->setCatalog($catalog)
            ->setName('Тестовая запись 1');
        $manager->persist($record1);
        $record2 = (new CatalogRecord())->setCatalog($catalog)
            ->setName('Тестовая запись 2');
        $manager->persist($record2);
        $record3 = (new CatalogRecord())->setCatalog($catalog)
            ->setName('Тестовая запись 3');
        $manager->persist($record3);
        $manager->flush();

        $attributesTableName = "catalog_{$catalog->getKey()}_record_attribute";
        $this->connection->executeQuery("DROP TABLE IF EXISTS {$attributesTableName}");
        $this->connection->executeQuery("CREATE SEQUENCE {$attributesTableName}_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->connection->executeQuery("CREATE TABLE IF NOT EXISTS {$attributesTableName} (
            id INT NOT NULL, 
            record_id INT NOT NULL, 
            attr_string TEXT,
            attr_boolean BOOLEAN
        )");
//        $this->connection->executeQuery("CREATE INDEX IDX_E2E624E1CC3C66FC ON {$attributesTableName} (record_id)");
        $this->connection->executeQuery("ALTER TABLE {$attributesTableName} ADD CONSTRAINT FK_E2E624E1CC3C66FC FOREIGN KEY (record_id) REFERENCES catalog_record (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->connection->executeQuery("INSERT INTO {$attributesTableName} (id, record_id, attr_string, attr_boolean) VALUES 
            (1, {$record1->getId()}, 'Тестовое значение string1', true),
            (2, {$record2->getId()}, 'Тестовое значение string2', false ),
            (3, {$record3->getId()}, NULL, NULL)
        ");
    }
}

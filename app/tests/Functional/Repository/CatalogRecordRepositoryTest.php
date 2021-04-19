<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DataFixtures\CatalogFixture;
use App\Entity\Catalog;
use App\Entity\CatalogRecord;
use App\Repository\CatalogRecordRepository;
use App\Repository\CatalogRepository;
use App\Tests\AbstractFunctionalTest;

final class CatalogRecordRepositoryTest extends AbstractFunctionalTest
{
    private CatalogRepository $catalogRepository;
    private CatalogRecordRepository $recordRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CatalogRecordRepository $repository */
        $repository = $this->entityManager->getRepository(CatalogRecord::class);
        $this->recordRepository = $repository;
        /** @var CatalogRepository $repository */
        $repository = $this->entityManager->getRepository(Catalog::class);
        $this->catalogRepository = $repository;
        $this->addFixture(new CatalogFixture($this->entityManager->getConnection()));
        $this->executeFixture();
    }

    public function testGetByAttributes(): void
    {
        $catalog = $this->catalogRepository->find(1);
        $result = $this->recordRepository->findByAttributes($catalog, ['boolean' => false]);
        self::assertCount(1, $result);
        self::assertEquals('Тестовая запись 2', current($result)['name']);
        self::assertEquals(['id' => 2, 'string' => 'Тестовое значение string2', 'boolean' => false], current($result)['attributes']);

        $result = $this->recordRepository->findByAttributes($catalog, ['boolean' => true]);
        self::assertCount(1, $result);
        self::assertEquals('Тестовая запись 1', current($result)['name']);
        self::assertEquals(['id' => 1, 'string' => 'Тестовое значение string1', 'boolean' => true], current($result)['attributes']);

        $result = $this->recordRepository->findByAttributes($catalog, ['boolean' => null]);
        self::assertCount(1, $result);
        self::assertEquals('Тестовая запись 3', current($result)['name']);
        self::assertEquals(['id' => 3, 'string' => null, 'boolean' => null], current($result)['attributes']);
    }
}

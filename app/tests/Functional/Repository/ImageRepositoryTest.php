<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DataFixtures\ImagesFixture;
use App\Entity\Image;
use App\Repository\ImageRepositoryInterface;
use App\Tests\AbstractFunctionalTest;

final class ImageRepositoryTest extends AbstractFunctionalTest
{
    private ImageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var ImageRepositoryInterface $repository */
        $repository = $this->entityManager->getRepository(Image::class);
        $this->repository = $repository;
        $this->addFixture(new ImagesFixture());
        $this->executeFixture();
    }

    public function testGetSuccess(): void
    {
        $pageSize = 10;
        $result = $this->repository->getSuccess(1, $pageSize);
        self::assertEquals(ImagesFixture::SUCCESS_COUNT, $result->count());
        self::assertCount($pageSize, $result->getIterator());
        $result = $this->repository->getSuccess(2, $pageSize);
        self::assertCount(ImagesFixture::SUCCESS_COUNT - $pageSize, $result->getIterator());
        self::assertEquals('success4', $result->getIterator()->current()->getName());
    }

    public function testGetFailed(): void
    {
        $pageSize = 10;
        $result = $this->repository->getFailed(1, $pageSize);
        self::assertEquals(ImagesFixture::FAILED_COUNT, $result->count());
        self::assertCount($pageSize, $result->getIterator());
        $result = $this->repository->getFailed(2, $pageSize);
        self::assertCount(ImagesFixture::FAILED_COUNT - $pageSize, $result->getIterator());
        self::assertEquals('failed7', $result->getIterator()->current()->getName());
    }
}

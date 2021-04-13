<?php

declare(strict_types=1);

namespace app\tests\Functional\Repository;

use App\DataFixtures\ImagesFixture;
use App\Entity\Image;
use App\Repository\ImageRepositoryInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImageRepositoryTest extends KernelTestCase
{
    private ?ContainerAwareLoader $fixtureLoader = null;
    private ?ORMExecutor $fixtureExecutor = null;

    private EntityManagerInterface $entityManager;
    private ImageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
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

    protected function addFixture(FixtureInterface $fixture): void
    {
        $this->getFixtureLoader()->addFixture($fixture);
    }

    protected function executeFixture(): void
    {
        $this->getFixtureExecutor()->execute($this->getFixtureLoader()->getFixtures());
    }

    private function getFixtureExecutor(): ORMExecutor
    {
        if (null === $this->fixtureExecutor) {
            $this->fixtureExecutor = new ORMExecutor($this->entityManager, new ORMPurger($this->entityManager));
        }

        return $this->fixtureExecutor;
    }

    private function getFixtureLoader(): ContainerAwareLoader
    {
        if (null === $this->fixtureLoader) {
            $this->fixtureLoader = new ContainerAwareLoader(self::$kernel->getContainer());
        }

        return $this->fixtureLoader;
    }
}

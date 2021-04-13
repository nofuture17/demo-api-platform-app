<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\DeleteController;
use App\Entity\Image;
use App\Service\ImageFileStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DeleteControllerTest extends KernelTestCase
{
    /**
     * @var ImageFileStorageInterface|MockObject
     */
    private ImageFileStorageInterface | MockObject $fileStorage;
    /**
     * @var EntityManagerInterface|MockObject
     */
    private EntityManagerInterface | MockObject $entityManager;

    public function testInvoke(): void
    {
        $image = new Image();
        $controller = $this->createController();
        $this->fileStorage->expects(self::once())
            ->method('removeImageFiles')
            ->with($image);
        $this->entityManager->expects(self::once())
            ->method('remove')
            ->with($image);
        $this->entityManager->expects(self::once())
            ->method('flush');
        $controller->__invoke($image);
    }

    private function createController(): DeleteController
    {
        $this->fileStorage = $this->createMock(ImageFileStorageInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        return new DeleteController($this->fileStorage, $this->entityManager);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Controller\GetFailedController;
use App\Entity\Image;
use App\Repository\ImageRepositoryInterface;
use App\Service\PaginatorFactoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class GetFailedControllerTest extends KernelTestCase
{
    private ImageRepositoryInterface | MockObject $imageRepository;
    private PaginatorFactoryInterface | MockObject $paginationFactory;

    public function testInvoke(): void
    {
        $controller = $this->createController();
        $paginator = $this->createMock(DoctrinePaginator::class);
        $expected = $this->createMock(PaginatorInterface::class);
        $request = $this->createMock(Request::class);
        $request->expects(self::once())
            ->method('get')
            ->with('page', 1)
            ->willReturn($page = 10);
        $this->imageRepository->expects(self::once())
            ->method('getFailed')
            ->with($page, Image::ITEMS_PER_PAGE)
            ->willReturn($paginator);
        $this->paginationFactory->expects(self::once())
            ->method('create')
            ->with($paginator)
            ->willReturn($expected);
        self::assertEquals($expected, $controller->__invoke($request));
    }

    private function createController(): GetFailedController
    {
        $this->imageRepository = $this->createMock(ImageRepositoryInterface::class);
        $this->paginationFactory = $this->createMock(PaginatorFactoryInterface::class);

        return new GetFailedController($this->imageRepository, $this->paginationFactory);
    }
}

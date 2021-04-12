<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Image;
use App\Repository\ImageRepositoryInterface;
use App\Service\PaginatorFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractListController
{
    public function __construct(
        protected ImageRepositoryInterface $imageRepository,
        protected PaginatorFactoryInterface $paginatorFactory
    ) {
    }

    /**
     * @return PaginatorInterface<Image>
     */
    abstract public function __invoke(Request $request): PaginatorInterface;
}

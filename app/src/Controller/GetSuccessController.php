<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Image;
use Symfony\Component\HttpFoundation\Request;

final class GetSuccessController extends AbstractListController
{
    public function __invoke(Request $request): PaginatorInterface
    {
        return $this->paginatorFactory->create($this->imageRepository->getSuccess(
            (int) $request->get('page', 1),
            Image::ITEMS_PER_PAGE
        ));
    }
}

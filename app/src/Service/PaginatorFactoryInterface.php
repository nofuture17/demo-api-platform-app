<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

interface PaginatorFactoryInterface
{
    /**
     * @param DoctrinePaginator<mixed> $doctrinePaginator
     *
     * @return PaginatorInterface<mixed>
     */
    public function create(DoctrinePaginator $doctrinePaginator): PaginatorInterface;
}

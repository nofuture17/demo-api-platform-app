<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * @codeCoverageIgnore
 */
final class PaginatorFactory implements PaginatorFactoryInterface
{
    public function create(DoctrinePaginator $doctrinePaginator): PaginatorInterface
    {
        return new Paginator($doctrinePaginator);
    }
}

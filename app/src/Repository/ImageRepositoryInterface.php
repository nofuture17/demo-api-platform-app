<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Image;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface ImageRepositoryInterface
{
    /**
     * @return Paginator<Image>
     */
    public function getSuccess(int $page, int $itemsPerPage): Paginator;

    /**
     * @return Paginator<Image>
     */
    public function getFailed(int $page, int $itemsPerPage): Paginator;
}

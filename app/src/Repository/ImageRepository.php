<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 */
class ImageRepository extends ServiceEntityRepository implements ImageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function getSuccess(int $page, int $itemsPerPage): Paginator
    {
        ($qb = $this->createQueryBuilder('i'))
            ->andWhere('i.error IS NULL');

        return new Paginator($this->applyPagination($qb, $page, $itemsPerPage));
    }

    public function getFailed(int $page, int $itemsPerPage): Paginator
    {
        $qb = $this->createQueryBuilder('i');
        $qb->andWhere('i.error IS NOT NULL');

        return new Paginator($this->applyPagination($qb, $page, $itemsPerPage));
    }

    private function applyPagination(QueryBuilder $qb, int $page, int $itemsPerPage): QueryBuilder
    {
        return $qb
            ->orderBy('i.id', 'DESC')
            ->setMaxResults($itemsPerPage)
            ->setFirstResult(($page - 1) * $itemsPerPage);
    }
}

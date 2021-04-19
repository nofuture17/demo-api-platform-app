<?php

namespace App\Repository;

use App\Entity\CatalogField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CatalogField|null find($id, $lockMode = null, $lockVersion = null)
 * @method CatalogField|null findOneBy(array $criteria, array $orderBy = null)
 * @method CatalogField[]    findAll()
 * @method CatalogField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatalogField::class);
    }

    // /**
    //  * @return CatalogField[] Returns an array of CatalogField objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CatalogField
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

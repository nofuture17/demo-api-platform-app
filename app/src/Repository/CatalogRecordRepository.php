<?php

namespace App\Repository;

use App\Doctrine\Persistence\Reflection\DynamicAttributeReflectionProperty;
use App\Entity\Catalog;
use App\Entity\CatalogRecord;
use App\Entity\CatalogRecordAttributes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CatalogRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method CatalogRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method CatalogRecord[]    findAll()
 * @method CatalogRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatalogRecord::class);
    }

    private function updateMapping(Catalog $catalog): void
    {
        $metadata = $this->_em->getClassMetadata(CatalogRecordAttributes::class);
        $metadata->table['name'] = "catalog_{$catalog->getKey()}_record_attribute";
        foreach ($catalog->getCatalogFields() as $field) {
            $metadata->fieldMappings[$field->getKey()] = [
                'fieldName' => $field->getKey(),
                'type' => $field->getType(),
                'columnName' => "attr_{$field->getKey()}",
            ];
        }

        // Для гидрации Entity
//        $metadata->reflFields['bool'] = new DynamicAttributeReflectionProperty(CatalogRecordAttributes::class, 'bool');
//        $metadata->reflFields['string'] = new DynamicAttributeReflectionProperty(CatalogRecordAttributes::class, 'string');
    }

    /**
     * @return CatalogRecord[]
     */
    public function findByAttributes(Catalog $catalog, array $attributes): array
    {
        $oldClass = clone $this->_class;
        $this->updateMapping($catalog);
        $qb = $this->createQueryBuilder($alias = 'cr')
            ->orderBy("{$alias}.id", 'ASC')
            ->leftJoin("{$alias}.attributes", $attrAlias = 'cra')
            ->addSelect($attrAlias)
        ;

        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                $qb->andWhere("{$attrAlias}.{$key} IS NULL");
                continue;
            }
            if (is_array($value)) {
                $qb->andWhere("{$attrAlias}.{$key} IN(:param_{$key})");
            } else {
                $qb->andWhere("{$attrAlias}.{$key} = :param_{$key}");
            }
            $qb->setParameter("param_{$key}", $value);
        }

        $result = $qb->getQuery()
            ->getArrayResult();

        $this->_class = $oldClass;

        return $result;
    }
}

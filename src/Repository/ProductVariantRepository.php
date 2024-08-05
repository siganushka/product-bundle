<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductVariant;

/**
 * @extends ServiceEntityRepository<ProductVariant>
 *
 * @method ProductVariant      createNew(...$args)
 * @method ProductVariant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductVariant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductVariant[]    findAll()
 * @method ProductVariant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVariantRepository extends GenericEntityRepository
{
    public function createQueryBuilder(string $alias, string $indexBy = null): QueryBuilder
    {
        $queryBuilder = parent::createQueryBuilder($alias, $indexBy);
        // Overwide default orderBy parts
        $queryBuilder->join(\sprintf('%s.product', $alias), 'p');
        $queryBuilder->orderBy('p.id', 'DESC');
        $queryBuilder->addOrderBy(\sprintf('%s.createdAt', $alias), 'ASC');
        $queryBuilder->addOrderBy(\sprintf('%s.id', $alias), 'ASC');

        return $queryBuilder;
    }
}

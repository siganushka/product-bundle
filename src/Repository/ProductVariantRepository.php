<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductVariant;

/**
 * @extends GenericEntityRepository<ProductVariant>
 */
class ProductVariantRepository extends GenericEntityRepository
{
    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        $queryBuilder = parent::createQueryBuilder($alias, $indexBy);
        // Override default orderBy parts
        $queryBuilder->join(\sprintf('%s.product', $alias), 'p');
        $queryBuilder->orderBy('p.id', 'DESC');
        $queryBuilder->addOrderBy(\sprintf('%s.createdAt', $alias), 'ASC');
        $queryBuilder->addOrderBy(\sprintf('%s.id', $alias), 'ASC');

        return $queryBuilder;
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductVariant;

/**
 * @template T of ProductVariant = ProductVariant
 *
 * @extends GenericEntityRepository<T>
 */
class ProductVariantRepository extends GenericEntityRepository
{
    public function createQueryBuilderWithEnabled(string $alias, bool $enabled = true): QueryBuilder
    {
        return $this->createQueryBuilderWithOrderBy($alias)
            ->andWhere(\sprintf('%s.enabled = :enabled', $alias))
            ->setParameter('enabled', $enabled)
        ;
    }
}

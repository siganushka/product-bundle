<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Dto\ProductQueryDto;
use Siganushka\ProductBundle\Entity\AbstractProduct;

/**
 * @template T of AbstractProduct = AbstractProduct
 *
 * @extends GenericEntityRepository<T>
 */
class ProductRepository extends GenericEntityRepository
{
    public function createQueryBuilderFromDto(string $alias, ProductQueryDto $dto): QueryBuilder
    {
        $criteria = self::createCriteriaFromDto($dto);

        $qb = $this->createQueryBuilderWithOrderBy($alias);
        $qb->addCriteria($criteria);

        return $qb;
    }
}

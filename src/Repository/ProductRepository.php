<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Dto\ProductQueryDto;
use Siganushka\ProductBundle\Entity\Product;

/**
 * @template T of Product = Product
 *
 * @extends GenericEntityRepository<T>
 */
class ProductRepository extends GenericEntityRepository
{
    public function createQueryBuilderByDto(string $alias, ProductQueryDto $dto): QueryBuilder
    {
        $criteria = Criteria::create(true);

        if ($dto->name) {
            $criteria->andWhere(Criteria::expr()->contains('name', $dto->name));
        }

        if ($dto->created?->startAt) {
            $criteria->andWhere(Criteria::expr()->gte('createdAt', $dto->created->startAt));
        }

        if ($dto->created?->endAt) {
            $criteria->andWhere(Criteria::expr()->lte('createdAt', $dto->created->endAt));
        }

        $queryBuilder = $this->createQueryBuilderWithOrderBy($alias);
        $queryBuilder->addCriteria($criteria);

        return $queryBuilder;
    }
}

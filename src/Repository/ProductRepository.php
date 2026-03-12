<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

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
        $queryBuilder = $this->createQueryBuilderWithOrderBy($alias);

        if ($dto->name) {
            $queryBuilder->andWhere(\sprintf('%s.name LIKE :name', $alias))->setParameter('name', '%'.$dto->name.'%');
        }

        if ($dto->created?->startAt) {
            $queryBuilder->andWhere(\sprintf('%s.createdAt >= :startAt', $alias))->setParameter('startAt', $dto->created->startAt);
        }

        if ($dto->created?->endAt) {
            $queryBuilder->andWhere(\sprintf('%s.createdAt <= :endAt', $alias))->setParameter('endAt', $dto->created->endAt);
        }

        return $queryBuilder;
    }
}

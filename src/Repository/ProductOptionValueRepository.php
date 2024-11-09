<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @extends GenericEntityRepository<ProductOptionValue>
 *
 * @method ProductOptionValue      createNew(...$args)
 * @method ProductOptionValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductOptionValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductOptionValue[]    findAll()
 * @method ProductOptionValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductOptionValueRepository extends GenericEntityRepository
{
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;

/**
 * @template T of AbstractProductOptionValue = AbstractProductOptionValue
 *
 * @extends GenericEntityRepository<T>
 */
class ProductOptionValueRepository extends GenericEntityRepository
{
}

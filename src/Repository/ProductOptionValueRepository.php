<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @template T of ProductOptionValue = ProductOptionValue
 *
 * @extends GenericEntityRepository<T>
 */
class ProductOptionValueRepository extends GenericEntityRepository
{
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductOption;

/**
 * @template T of ProductOption = ProductOption
 *
 * @extends GenericEntityRepository<T>
 */
class ProductOptionRepository extends GenericEntityRepository
{
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\AbstractProductOption;

/**
 * @template T of AbstractProductOption = AbstractProductOption
 *
 * @extends GenericEntityRepository<T>
 */
class ProductOptionRepository extends GenericEntityRepository
{
}

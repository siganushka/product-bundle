<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductVariant;

/**
 * @template T of ProductVariant = ProductVariant
 *
 * @extends GenericEntityRepository<T>
 */
class ProductVariantRepository extends GenericEntityRepository
{
}

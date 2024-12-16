<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\Product;

/**
 * @template T of Product = Product
 *
 * @extends GenericEntityRepository<T>
 */
class ProductRepository extends GenericEntityRepository
{
}

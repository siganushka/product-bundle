<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Fixtures;

use Siganushka\ProductBundle\Entity\AbstractProduct;

/**
 * @extends AbstractProduct<TestMedia, TestProductOption, TestProductVariant>
 */
class TestProduct extends AbstractProduct
{
}

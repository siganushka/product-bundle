<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductOptionValueTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new ProductOptionValue(text: 'foo');

        static::assertSame('foo', $entity->getText());
        static::assertNotNull($entity->getCode());
    }
}

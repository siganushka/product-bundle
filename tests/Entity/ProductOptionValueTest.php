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
        static::assertNotNull($entity->getCode());
        static::assertSame('foo', $entity->getText());

        $entity = new ProductOptionValue('test', 'bar');
        static::assertSame('test', $entity->getCode());
        static::assertSame('bar', $entity->getText());
    }
}

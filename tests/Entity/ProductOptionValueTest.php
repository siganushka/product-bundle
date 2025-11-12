<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductOptionValue;

class ProductOptionValueTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new FooProductOptionValue(text: 'foo');
        static::assertNotNull($entity->getCode());
        static::assertSame('foo', $entity->getText());

        $entity = new FooProductOptionValue('test', 'bar');
        static::assertSame('test', $entity->getCode());
        static::assertSame('bar', $entity->getText());
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductVariant;

class ProductVariantTest extends TestCase
{
    public function testAll(): void
    {
        $variant = new FooProductVariant();
        static::assertNull($variant->getCode());
        static::assertNull($variant->getName());

        $choice = ProductVariantChoice::create(
            new FooProductOptionValue('foo', 'aaa'),
            new FooProductOptionValue('bar', 'bbb')
        );

        $variant = new FooProductVariant($choice);
        static::assertSame('bar-foo', $variant->getCode());
        static::assertSame('aaa/bbb', $variant->getName());
    }
}

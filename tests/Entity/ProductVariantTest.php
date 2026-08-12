<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;

class ProductVariantTest extends TestCase
{
    public function testAll(): void
    {
        $variant = new TestProductVariant();
        static::assertNull($variant->getCode());
        static::assertNull($variant->getName());

        $choice = ProductVariantChoice::create(
            new TestProductOptionValue('foo', 'aaa'),
            new TestProductOptionValue('bar', 'bbb')
        );

        $variant = new TestProductVariant($choice);
        static::assertSame('bar-foo', $variant->getCode());
        static::assertSame('aaa/bbb', $variant->getName());
    }
}

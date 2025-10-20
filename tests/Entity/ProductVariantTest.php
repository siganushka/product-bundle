<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;

class ProductVariantTest extends TestCase
{
    public function testAll(): void
    {
        $variant = new ProductVariant();
        static::assertNull($variant->getCode());
        static::assertNull($variant->getName());

        $choice = ProductVariantChoice::create(
            new ProductOptionValue('foo', 'aaa'),
            new ProductOptionValue('bar', 'bbb')
        );

        $variant = new ProductVariant($choice);
        static::assertSame('bar-foo', $variant->getCode());
        static::assertSame('aaa/bbb', $variant->getName());
    }
}

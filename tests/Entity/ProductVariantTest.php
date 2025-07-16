<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;

class ProductVariantTest extends TestCase
{
    public function testAll(): void
    {
        $variant = new ProductVariant();
        static::assertNull($variant->getName());

        $variant = new ProductVariant();
        $variant->setProduct(new Product('hello'));
        static::assertSame('hello', $variant->getName());

        $v1 = new ProductOptionValue(text: 'aaa');
        $v2 = new ProductOptionValue(text: 'bbb');

        $variant = new ProductVariant(new ProductVariantChoice([$v1, $v2]));
        static::assertSame('aaa/bbb', $variant->getName());

        $variant->setProduct(new Product('world'));
        static::assertSame('world【aaa/bbb】', $variant->getName());
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;

class ProductVariantTest extends TestCase
{
    public function testAll(): void
    {
        $variant = new ProductVariant();
        static::assertNull($variant->getName());

        $variant = new ProductVariant(new Product('hello'));
        static::assertSame('hello', $variant->getName());

        $v1 = new ProductOptionValue(text: 'aaa');
        $v1->setOption(new ProductOption('foo'));

        $v2 = new ProductOptionValue(text: 'bbb');
        $v2->setOption(new ProductOption('bar'));

        $variant = new ProductVariant(choice: new ProductVariantChoice([$v1, $v2]));
        static::assertSame('foo: aaa, bar: bbb', $variant->getName());

        $variant->setProduct(new Product('world'));
        static::assertSame('world【foo: aaa, bar: bbb】', $variant->getName());
    }
}

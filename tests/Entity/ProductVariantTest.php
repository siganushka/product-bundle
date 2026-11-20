<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;

class ProductVariantTest extends TestCase
{
    public function testAll(): void
    {
        $variant = new TestProductVariant();
        static::assertSame(ProductVariantChoice::DEFAULT_CODE, $variant->getCode());
        static::assertNull($variant->getName());
        static::assertNull($variant->getProduct());
        static::assertNull($variant->getPrice());
        static::assertNull($variant->getStock());
        static::assertCount(0, $variant->getOptionValues());

        $choice = ProductVariantChoice::create(
            new TestProductOptionValue('foo', 'aaa'),
            new TestProductOptionValue('bar', 'bbb')
        );

        $variant = new TestProductVariant($choice);
        static::assertSame('bar-foo', $variant->getCode());
        static::assertSame('aaa/bbb', $variant->getName());
        static::assertCount(2, $variant->getOptionValues());
    }

    public function testProduct(): void
    {
        $variant = new TestProductVariant();
        $product = new TestProduct();

        $variant->setProduct($product);
        static::assertSame($product, $variant->getProduct());

        $variant->setProduct(null);
        static::assertNull($variant->getProduct());
    }

    public function testPriceAndStock(): void
    {
        $variant = new TestProductVariant();

        $variant->setPrice(100);
        $variant->setStock(5);

        static::assertSame(100, $variant->getPrice());
        static::assertSame(5, $variant->getStock());

        $variant->setPrice(null);
        $variant->setStock(null);

        static::assertNull($variant->getPrice());
        static::assertNull($variant->getStock());
    }

    public function testResetName(): void
    {
        $value = new TestProductOptionValue('red', 'Red');
        $variant = new TestProductVariant(ProductVariantChoice::create($value));

        $value->setName('Crimson');

        // 清除已生成的名称缓存后按新的选项值重新计算
        $variant->resetName();
        static::assertSame('Crimson', $variant->getName());
    }
}

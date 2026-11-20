<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;

class ProductTest extends TestCase
{
    public function testAll(): void
    {
        $product = new TestProduct();
        static::assertNull($product->getName());
        static::assertNull($product->getSummary());
        static::assertNull($product->getImg());
        static::assertNull($product->getLowestPrice());
        static::assertNull($product->getHighestPrice());
        static::assertNull($product->getMinLimit());
        static::assertNull($product->getMaxLimit());
        static::assertCount(0, $product->getOptions());
        static::assertCount(0, $product->getVariants());

        $product = new TestProduct('foo');
        static::assertSame('foo', $product->getName());

        $media = new TestMedia()->setHash('hash');
        $product
            ->setName('bar')
            ->setSummary('baz')
            ->setImg($media)
            ->setLowestPrice(100)
            ->setHighestPrice(300)
            ->setMinLimit(1)
            ->setMaxLimit(10)
        ;

        static::assertSame('bar', $product->getName());
        static::assertSame('baz', $product->getSummary());
        static::assertSame($media, $product->getImg());
        static::assertSame(100, $product->getLowestPrice());
        static::assertSame(300, $product->getHighestPrice());
        static::assertSame(1, $product->getMinLimit());
        static::assertSame(10, $product->getMaxLimit());
    }

    public function testOptions(): void
    {
        $product = new TestProduct();
        $option = new TestProductOption('Color');

        $product->addOption($option);
        static::assertCount(1, $product->getOptions());
        static::assertSame($product, $option->getProduct());

        // 重复添加不会产生重复记录
        $product->addOption($option);
        static::assertCount(1, $product->getOptions());

        $product->removeOption($option);
        static::assertCount(0, $product->getOptions());
        static::assertNull($option->getProduct());

        // 移除不属于当前商品的 option 时不清空其归属
        $other = new TestProduct();
        $otherOption = new TestProductOption('Size');
        $other->addOption($otherOption);

        $product->removeOption($otherOption);
        static::assertCount(0, $product->getOptions());
        static::assertSame($other, $otherOption->getProduct());
    }

    public function testVariants(): void
    {
        $product = new TestProduct();
        $variant = new TestProductVariant($this->createChoice('red', 'Red'));

        $product->addVariant($variant);
        static::assertCount(1, $product->getVariants());
        static::assertSame($product, $variant->getProduct());

        // 相同 code 的变体不会重复添加
        $duplicated = new TestProductVariant($this->createChoice('red', 'Red'));
        $product->addVariant($duplicated);
        static::assertCount(1, $product->getVariants());
        static::assertNull($duplicated->getProduct());

        $product->removeVariant($variant);
        static::assertCount(0, $product->getVariants());
        static::assertNull($variant->getProduct());

        // 移除不属于当前商品的变体时不清空其归属
        $other = new TestProduct();
        $otherVariant = new TestProductVariant();
        $other->addVariant($otherVariant);

        $product->removeVariant($otherVariant);
        static::assertCount(0, $product->getVariants());
        static::assertSame($other, $otherVariant->getProduct());
    }

    private function createChoice(string $code, string $name): ProductVariantChoice
    {
        return ProductVariantChoice::create(new TestProductOptionValue($code, $name));
    }
}

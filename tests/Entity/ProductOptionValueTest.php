<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;

class ProductOptionValueTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new TestProductOptionValue(name: 'foo');
        static::assertSame('foo', $entity->getName());
        static::assertNull($entity->getOption());
        static::assertNull($entity->getImg());
        static::assertCount(0, $entity->getVariants());
        static::assertSame(0, $entity->getVariantsCount());

        $entity = new TestProductOptionValue(code: 'test', name: 'bar');
        static::assertSame('test', $entity->getCode());
        static::assertSame('bar', $entity->getName());
    }

    public function testOption(): void
    {
        $value = new TestProductOptionValue('red', 'Red');
        $option = new TestProductOption('Color');

        $value->setOption($option);
        static::assertSame($option, $value->getOption());

        $value->setOption(null);
        static::assertNull($value->getOption());
    }

    public function testImg(): void
    {
        $value = new TestProductOptionValue('red', 'Red');
        $media = new TestMedia();

        $value->setImg($media);
        static::assertSame($media, $value->getImg());

        $value->setImg(null);
        static::assertNull($value->getImg());
    }

    public function testVariantsCountOnlyCountsEnabledVariants(): void
    {
        $value = new TestProductOptionValue('red', 'Red');

        $enabled = new TestProductVariant(ProductVariantChoice::create($value));
        $disabled = new TestProductVariant(ProductVariantChoice::create($value));
        $disabled->setEnabled(false);

        /** @var Collection<int, TestProductVariant> $variants */
        $variants = (new \ReflectionProperty($value, 'variants'))->getValue($value);
        $variants->add($enabled);
        $variants->add($disabled);

        static::assertCount(2, $value->getVariants());
        static::assertSame(1, $value->getVariantsCount());
    }

    public function testSetNameResetsVariantName(): void
    {
        $value = new TestProductOptionValue('red', 'Red');
        $variant = new TestProductVariant(ProductVariantChoice::create($value));

        /** @var Collection<int, TestProductVariant> $variants */
        $variants = (new \ReflectionProperty($value, 'variants'))->getValue($value);
        $variants->add($variant);

        static::assertSame('Red', $variant->getName());

        $value->setName('Crimson');
        static::assertSame('Crimson', $variant->getName());
    }
}

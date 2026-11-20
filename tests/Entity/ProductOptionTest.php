<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;

class ProductOptionTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new TestProductOption();
        static::assertNull($entity->getName());
        static::assertNull($entity->getProduct());
        static::assertCount(0, $entity->getValues());

        $entity->setName('foo');
        static::assertSame('foo', $entity->getName());

        $entity->addValue(new TestProductOptionValue(name: 'bar'));
        static::assertCount(1, $entity->getValues());
    }

    public function testProduct(): void
    {
        $entity = new TestProductOption('Color');
        $product = new TestProduct();

        $entity->setProduct($product);
        static::assertSame($product, $entity->getProduct());

        $entity->setProduct(null);
        static::assertNull($entity->getProduct());
    }

    public function testRemoveValue(): void
    {
        $option = new TestProductOption('Color');
        $value = new TestProductOptionValue('red', 'Red');

        $option->addValue($value);
        static::assertSame($option, $value->getOption());

        $option->removeValue($value);
        static::assertCount(0, $option->getValues());
        static::assertNull($value->getOption());

        // 移除不属于当前 option 的 value 时不清空其归属
        $other = new TestProductOption('Size');
        $otherValue = new TestProductOptionValue('s', 'S');
        $other->addValue($otherValue);

        $option->removeValue($otherValue);
        static::assertCount(0, $option->getValues());
        static::assertSame($other, $otherValue->getOption());
    }

    public function testClone(): void
    {
        $po = new TestProductOption();
        $po->addValue(new TestProductOptionValue(name: 'foo'));
        $po->addValue(new TestProductOptionValue(name: 'bar'));
        $po->addValue(new TestProductOptionValue(name: 'baz'));

        (new \ReflectionProperty($po, 'id'))->setValue($po, 1);

        $po2 = clone $po;

        static::assertNull($po2->getId());
        static::assertNotSame($po->getValues(), $po2->getValues());
        static::assertNotSame($po->getValues()[0], $po2->getValues()[0]);
        static::assertNotSame($po->getValues()[1], $po2->getValues()[1]);
        static::assertNotSame($po->getValues()[2], $po2->getValues()[2]);
    }
}

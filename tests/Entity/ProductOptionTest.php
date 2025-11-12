<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductOptionValue;

class ProductOptionTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new FooProductOption();
        static::assertNull($entity->getName());
        static::assertCount(0, $entity->getValues());

        $entity->setName('foo');
        static::assertSame('foo', $entity->getName());

        $entity->addValue(new FooProductOptionValue('bar'));
        static::assertCount(1, $entity->getValues());
    }

    public function testClone(): void
    {
        $po = new FooProductOption();
        $po->addValue(new FooProductOptionValue(null, 'foo'));
        $po->addValue(new FooProductOptionValue(null, 'bar'));
        $po->addValue(new FooProductOptionValue(null, 'baz'));

        $po2 = clone $po;
        static::assertNotSame($po->getValues(), $po2->getValues());
        static::assertNotSame($po->getValues()[0], $po2->getValues()[0]);
        static::assertNotSame($po->getValues()[1], $po2->getValues()[1]);
        static::assertNotSame($po->getValues()[2], $po2->getValues()[2]);
    }
}

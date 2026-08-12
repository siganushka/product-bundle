<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;

class ProductOptionTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new TestProductOption();
        static::assertNull($entity->getName());
        static::assertCount(0, $entity->getValues());

        $entity->setName('foo');
        static::assertSame('foo', $entity->getName());

        $entity->addValue(new TestProductOptionValue(text: 'bar'));
        static::assertCount(1, $entity->getValues());
    }

    public function testClone(): void
    {
        $po = new TestProductOption();
        $po->addValue(new TestProductOptionValue(text: 'foo'));
        $po->addValue(new TestProductOptionValue(text: 'bar'));
        $po->addValue(new TestProductOptionValue(text: 'baz'));

        (new \ReflectionProperty($po, 'id'))->setValue($po, 1);

        $po2 = clone $po;

        static::assertNull($po2->getId());
        static::assertNotSame($po->getValues(), $po2->getValues());
        static::assertNotSame($po->getValues()[0], $po2->getValues()[0]);
        static::assertNotSame($po->getValues()[1], $po2->getValues()[1]);
        static::assertNotSame($po->getValues()[2], $po2->getValues()[2]);
    }
}

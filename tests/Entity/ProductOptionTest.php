<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductOptionTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new ProductOption();
        static::assertNull($entity->getName());
        static::assertCount(0, $entity->getValues());

        $entity->setName('foo');
        static::assertSame('foo', $entity->getName());

        $entity->addValue(new ProductOptionValue('bar'));
        static::assertCount(1, $entity->getValues());
    }
}

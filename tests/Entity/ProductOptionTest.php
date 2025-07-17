<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductOptionTest extends TestCase
{
    public function testClone(): void
    {
        $po = new ProductOption();
        $po->addValue(new ProductOptionValue(null, 'foo'));
        $po->addValue(new ProductOptionValue(null, 'bar'));
        $po->addValue(new ProductOptionValue(null, 'baz'));

        $po2 = clone $po;
        static::assertNotSame($po->getValues(), $po2->getValues());
        static::assertNotSame($po->getValues()[0], $po2->getValues()[0]);
        static::assertNotSame($po->getValues()[1], $po2->getValues()[1]);
        static::assertNotSame($po->getValues()[2], $po2->getValues()[2]);
    }
}

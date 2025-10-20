<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductTest extends TestCase
{
    public function testGenerateChoices(): void
    {
        $entity = new Product();

        $choices = $entity->generateChoices();
        static::assertCount(1, $choices);
        static::assertNull($choices[0]->code);
        static::assertNull($choices[0]->name);

        $option1 = new ProductOption('foo');
        $option1->addValue(new ProductOptionValue('f1', 'foo-1'));
        $option1->addValue(new ProductOptionValue('f2', 'foo-2'));

        $option2 = new ProductOption('bar');
        $option2->addValue(new ProductOptionValue('b1', 'bar-1'));
        $option2->addValue(new ProductOptionValue('b2', 'bar-2'));
        $option2->addValue(new ProductOptionValue('b3', 'bar-3'));

        $entity->addOption($option1);
        $entity->addOption($option2);

        $choices = $entity->generateChoices();
        static::assertCount(6, $choices);
        static::assertSame('b1-f1', $choices[0]->code);
        static::assertSame('b2-f1', $choices[1]->code);
        static::assertSame('b3-f1', $choices[2]->code);
        static::assertSame('b1-f2', $choices[3]->code);
        static::assertSame('b2-f2', $choices[4]->code);
        static::assertSame('b3-f2', $choices[5]->code);
        static::assertSame('foo-1/bar-1', $choices[0]->name);
        static::assertSame('foo-1/bar-2', $choices[1]->name);
        static::assertSame('foo-1/bar-3', $choices[2]->name);
        static::assertSame('foo-2/bar-1', $choices[3]->name);
        static::assertSame('foo-2/bar-2', $choices[4]->name);
        static::assertSame('foo-2/bar-3', $choices[5]->name);
    }
}

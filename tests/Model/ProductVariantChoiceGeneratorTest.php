<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Model\ProductVariantChoiceGenerator;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;

class ProductVariantChoiceGeneratorTest extends TestCase
{
    public function testAll(): void
    {
        $po1 = new TestProductOption('po1');

        $po2 = new TestProductOption('po2');
        $po2->addValue(new TestProductOptionValue(code: 'a1', name: 'foo'));
        $po2->addValue(new TestProductOptionValue(code: 'a2', name: 'bar'));
        $po2->addValue(new TestProductOptionValue(code: 'a3', name: 'baz'));

        $po3 = new TestProductOption('po3');
        $po3->addValue(new TestProductOptionValue(code: 'z1', name: 'x'));
        $po3->addValue(new TestProductOptionValue(code: 'z2', name: 'y'));
        $po3->addValue(new TestProductOptionValue(code: 'z3', name: 'z'));

        static::assertCount(1, $choices = ProductVariantChoiceGenerator::generate([]));
        static::assertNull($choices[0]->code);
        static::assertNull($choices[0]->name);

        static::assertCount(1, $choices = ProductVariantChoiceGenerator::generate([$po1]));
        static::assertNull($choices[0]->code);
        static::assertNull($choices[0]->name);

        static::assertCount(3, $choices = ProductVariantChoiceGenerator::generate([$po2]));
        static::assertSame('a1', $choices[0]->code);
        static::assertSame('a2', $choices[1]->code);
        static::assertSame('a3', $choices[2]->code);
        static::assertSame('foo', $choices[0]->name);
        static::assertSame('bar', $choices[1]->name);
        static::assertSame('baz', $choices[2]->name);

        static::assertCount(9, $choices = ProductVariantChoiceGenerator::generate([$po3, $po2]));
        static::assertSame('a1-z1', $choices[0]->code);
        static::assertSame('a2-z1', $choices[1]->code);
        static::assertSame('a3-z1', $choices[2]->code);
        static::assertSame('a1-z2', $choices[3]->code);
        static::assertSame('a2-z2', $choices[4]->code);
        static::assertSame('a3-z2', $choices[5]->code);
        static::assertSame('a1-z3', $choices[6]->code);
        static::assertSame('a2-z3', $choices[7]->code);
        static::assertSame('a3-z3', $choices[8]->code);
        static::assertSame('x/foo', $choices[0]->name);
        static::assertSame('x/bar', $choices[1]->name);
        static::assertSame('x/baz', $choices[2]->name);
        static::assertSame('y/foo', $choices[3]->name);
        static::assertSame('y/bar', $choices[4]->name);
        static::assertSame('y/baz', $choices[5]->name);
        static::assertSame('z/foo', $choices[6]->name);
        static::assertSame('z/bar', $choices[7]->name);
        static::assertSame('z/baz', $choices[8]->name);
    }
}

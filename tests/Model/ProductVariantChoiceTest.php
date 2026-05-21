<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Model\ProductVariantChoice;

class ProductVariantChoiceTest extends TestCase
{
    public function testAll(): void
    {
        $choice = new ProductVariantChoice();
        static::assertCount(0, $choice->combinedOptionValues);
        static::assertNull($choice->code);
        static::assertNull($choice->name);

        $foo = new ProductOptionValue('a', 'foo');
        $bar = new ProductOptionValue('b', 'bar');
        $baz = new ProductOptionValue('c', 'baz');

        $choice = new ProductVariantChoice([$foo, $bar, $baz]);
        static::assertCount(3, $choice->combinedOptionValues);
        static::assertSame('a-b-c', $choice->code);
        static::assertSame('foo/bar/baz', $choice->name);
        static::assertSame($foo, $choice->combinedOptionValues[0]);
        static::assertSame($bar, $choice->combinedOptionValues[1]);
        static::assertSame($baz, $choice->combinedOptionValues[2]);

        $choice = ProductVariantChoice::create($baz, $bar, $foo);
        static::assertCount(3, $choice->combinedOptionValues);
        static::assertSame('a-b-c', $choice->code);
        static::assertSame('baz/bar/foo', $choice->name);
        static::assertSame($baz, $choice->combinedOptionValues[0]);
        static::assertSame($bar, $choice->combinedOptionValues[1]);
        static::assertSame($foo, $choice->combinedOptionValues[2]);
    }
}

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
        static::assertNull($choice->value);
        static::assertNull($choice->label);
        static::assertSame([], $choice->combinedOptionValues);

        $foo = new ProductOptionValue('a', 'foo');
        $bar = new ProductOptionValue('b', 'bar');
        $baz = new ProductOptionValue('c', 'baz');

        $choice = new ProductVariantChoice($foo, $bar, $baz);
        static::assertSame('a-b-c', $choice->value);
        static::assertSame('foo/bar/baz', $choice->label);
        static::assertSame($foo, $choice->combinedOptionValues[0]);
        static::assertSame($bar, $choice->combinedOptionValues[1]);
        static::assertSame($baz, $choice->combinedOptionValues[2]);

        $choice = new ProductVariantChoice($baz, $bar, $foo);
        static::assertSame('a-b-c', $choice->value);
        static::assertSame('baz/bar/foo', $choice->label);
        static::assertSame($baz, $choice->combinedOptionValues[0]);
        static::assertSame($bar, $choice->combinedOptionValues[1]);
        static::assertSame($foo, $choice->combinedOptionValues[2]);
    }
}

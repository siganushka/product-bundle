<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Model;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Model\ProductVariantChoice;

class ProductVariantChoiceTest extends TestCase
{
    public function testAll(): void
    {
        $choice = new ProductVariantChoice();
        static::assertInstanceOf(Collection::class, $choice);
        static::assertCount(0, $choice);
        static::assertNull($choice->value);
        static::assertNull($choice->label);

        $foo = new ProductOptionValue('a', 'foo');
        $bar = new ProductOptionValue('b', 'bar');
        $baz = new ProductOptionValue('c', 'baz');

        $choice = new ProductVariantChoice([$foo, $bar, $baz]);
        static::assertCount(3, $choice);
        static::assertSame('a-b-c', $choice->value);
        static::assertSame('foo/bar/baz', $choice->label);
        static::assertSame($foo, $choice[0]);
        static::assertSame($bar, $choice[1]);
        static::assertSame($baz, $choice[2]);

        $choice = ProductVariantChoice::create($baz, $bar, $foo);
        static::assertCount(3, $choice);
        static::assertSame('a-b-c', $choice->value);
        static::assertSame('baz/bar/foo', $choice->label);
        static::assertSame($baz, $choice[0]);
        static::assertSame($bar, $choice[1]);
        static::assertSame($foo, $choice[2]);
    }
}

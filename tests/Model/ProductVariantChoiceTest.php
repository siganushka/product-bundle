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

        $optionValues = [
            new ProductOptionValue('a', 'foo'),
            new ProductOptionValue('b', 'bar'),
            new ProductOptionValue('c', 'baz'),
        ];

        $choice = new ProductVariantChoice($optionValues);
        static::assertSame('a-b-c', $choice->value);
        static::assertSame('foo/bar/baz', $choice->label);
        static::assertSame($optionValues, $choice->combinedOptionValues);

        rsort($optionValues);

        $choice = new ProductVariantChoice($optionValues);
        static::assertSame('a-b-c', $choice->value);
        static::assertSame('baz/bar/foo', $choice->label);
        static::assertSame($optionValues, $choice->combinedOptionValues);
    }

    public function testUnexpectedValueException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected argument of type "Siganushka\ProductBundle\Entity\ProductOptionValue", "stdClass" given');

        /* @phpstan-ignore-next-line */
        new ProductVariantChoice([new \stdClass()]);
    }
}

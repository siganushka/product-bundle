<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductOptionValueTest extends TestCase
{
    /**
     * @dataProvider productOptionValuesProvider
     */
    public function testAll(?string $optionName, ?string $text, ?string $descriptor): void
    {
        $optionValue = new ProductOptionValue(text: $text);
        $optionValue->setOption(new ProductOption($optionName));

        static::assertSame($descriptor, $optionValue->getDescriptor());
    }

    public static function productOptionValuesProvider(): iterable
    {
        return [
            [null, null, null],
            ['', null, null],
            [null, '', ''],
            ['foo', null, null],
            [null, 'foo', 'foo'],
            ['foo', 'bar', 'foo: bar'],
            ['bar', 'foo', 'bar: foo'],
        ];
    }
}

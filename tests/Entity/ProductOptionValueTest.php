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
    public function testAll(?string $name, ?string $text, ?string $toString): void
    {
        $value = new ProductOptionValue(text: $text);
        $value->setOption(new ProductOption($name));

        static::assertSame($toString, $value->__toString());
    }

    public function testCodeInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The code with value "a@b" contains illegal character(s)');

        new ProductOptionValue('a@b');
    }

    public static function productOptionValuesProvider(): iterable
    {
        return [
            [null, null, ''],
            ['', null, ''],
            [null, '', ''],
            ['foo', null, ''],
            [null, 'foo', 'foo'],
            ['foo', 'bar', 'foo: bar'],
            ['bar', 'foo', 'bar: foo'],
        ];
    }
}

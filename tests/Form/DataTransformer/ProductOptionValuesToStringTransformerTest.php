<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductOptionValuesToStringTransformerTest extends TestCase
{
    public function testTansform(): void
    {
        $values = [
            new ProductOptionValue('1', 'AAA'),
            new ProductOptionValue('2', 'BBB'),
            new ProductOptionValue('3', 'CCC'),
        ];

        $transformer = new ProductOptionValuesToStringTransformer(',');
        static::assertSame('AAA,BBB,CCC', $transformer->transform($values));
        static::assertSame('AAA,BBB,CCC', $transformer->transform(new ArrayCollection($values)));
    }

    public function testReverseTransform(): void
    {
        $transformer = new ProductOptionValuesToStringTransformer('/');

        $values = $transformer->reverseTransform('a,b,c');
        static::assertCount(1, $values);
        static::assertInstanceOf(ProductOptionValue::class, $values[0]);
        static::assertSame('a,b,c', $values[0]->getText());

        $values = $transformer->reverseTransform('a          / b / c/  ');
        static::assertCount(3, $values);
        static::assertInstanceOf(ProductOptionValue::class, $values[0]);
        static::assertInstanceOf(ProductOptionValue::class, $values[1]);
        static::assertInstanceOf(ProductOptionValue::class, $values[2]);
        static::assertSame('a', $values[0]->getText());
        static::assertSame('b', $values[1]->getText());
        static::assertSame('c', $values[2]->getText());
    }

    public function testTansformNull(): void
    {
        $transformer = new ProductOptionValuesToStringTransformer(',');
        static::assertNull($transformer->transform(null));
    }

    public function testTansformEmptyArray(): void
    {
        $transformer = new ProductOptionValuesToStringTransformer(',');
        static::assertSame('', $transformer->transform([]));
    }

    public function testTansformExceptionOnInvalidString(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected an array.');

        $transformer = new ProductOptionValuesToStringTransformer(',');
        $transformer->transform('   ');
    }

    public function testTansformExceptionOnInvalidArray(): void
    {
        $this->expectException(\TypeError::class);

        $transformer = new ProductOptionValuesToStringTransformer(',');
        $transformer->transform([new \stdClass()]);
    }

    public function testReverseTransformNull(): void
    {
        $transformer = new ProductOptionValuesToStringTransformer(',');
        static::assertSame([], $transformer->reverseTransform(null));
    }

    public function testReverseTransformEmptyString(): void
    {
        $transformer = new ProductOptionValuesToStringTransformer(',');
        static::assertSame([], $transformer->reverseTransform('    '));
    }

    public function testReverseTransformException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        $transformer = new ProductOptionValuesToStringTransformer(',');
        static::assertSame([], $transformer->reverseTransform([]));
    }
}

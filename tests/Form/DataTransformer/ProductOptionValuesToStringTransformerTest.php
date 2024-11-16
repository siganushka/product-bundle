<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
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

        $transformer = $this->createTransformer(',');
        static::assertSame('AAA,BBB,CCC', $transformer->transform($values));
        static::assertSame('AAA,BBB,CCC', $transformer->transform(new ArrayCollection($values)));
    }

    public function testReverseTransform(): void
    {
        $transformer = $this->createTransformer('/');

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
        $transformer = $this->createTransformer(',');
        static::assertNull($transformer->transform(null));
    }

    public function testTansformEmptyArray(): void
    {
        $transformer = $this->createTransformer(',');
        static::assertSame('', $transformer->transform([]));
    }

    public function testTansformExceptionOnInvalidString(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected an array or Traversable.');

        $transformer = $this->createTransformer(',');
        $transformer->transform('   ');
    }

    public function testTansformExceptionOnInvalidArray(): void
    {
        $this->expectException(\TypeError::class);

        $transformer = $this->createTransformer(',');
        $transformer->transform([new \stdClass()]);
    }

    public function testReverseTransformNull(): void
    {
        $transformer = $this->createTransformer(',');
        static::assertSame([], $transformer->reverseTransform(null));
    }

    public function testReverseTransformEmptyString(): void
    {
        $transformer = $this->createTransformer(',');
        static::assertSame([], $transformer->reverseTransform('    '));
    }

    public function testReverseTransformException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        $transformer = $this->createTransformer(',');
        static::assertSame([], $transformer->reverseTransform([]));
    }

    /**
     * @param non-empty-string $separator
     */
    private function createTransformer(string $separator): ProductOptionValuesToStringTransformer
    {
        /** @var MockObject&ProductOptionValueRepository */
        $repository = $this->createMock(ProductOptionValueRepository::class);
        $repository->expects(static::any())
            ->method('createNew')
            ->willReturnCallback(fn (...$args) => new ProductOptionValue(...$args))
        ;

        return new ProductOptionValuesToStringTransformer($repository, $separator);
    }
}

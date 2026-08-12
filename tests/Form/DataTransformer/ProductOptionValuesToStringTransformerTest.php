<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductOptionValuesToStringTransformerTest extends TestCase
{
    public function testTansform(): void
    {
        $values = [
            new TestProductOptionValue('1', 'AAA'),
            new TestProductOptionValue('2', 'BBB'),
            new TestProductOptionValue('3', 'CCC'),
        ];

        $transformer = $this->createTransformer();
        static::assertSame('AAA,BBB,CCC', $transformer->transform($values));
        static::assertSame('AAA,BBB,CCC', $transformer->transform(new ArrayCollection($values)));
    }

    public function testReverseTransform(): void
    {
        $transformer = $this->createTransformer('/');

        $values = $transformer->reverseTransform('a,b,c');
        static::assertCount(1, $values);
        static::assertInstanceOf(TestProductOptionValue::class, $values[0]);
        static::assertSame('a,b,c', $values[0]->getText());

        $values = $transformer->reverseTransform('a          / b / c/  ');
        static::assertCount(3, $values);
        static::assertInstanceOf(TestProductOptionValue::class, $values[0]);
        static::assertInstanceOf(TestProductOptionValue::class, $values[1]);
        static::assertInstanceOf(TestProductOptionValue::class, $values[2]);
        static::assertSame('a', $values[0]->getText());
        static::assertSame('b', $values[1]->getText());
        static::assertSame('c', $values[2]->getText());
    }

    public function testTansformNull(): void
    {
        $transformer = $this->createTransformer();
        static::assertNull($transformer->transform(null));
    }

    public function testTansformEmptyArray(): void
    {
        $transformer = $this->createTransformer();
        static::assertSame('', $transformer->transform([]));
    }

    public function testTansformExceptionOnInvalidString(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected an array or Traversable.');

        $transformer = $this->createTransformer();
        /* @phpstan-ignore argument.type */
        $transformer->transform('   ');
    }

    public function testTansformExceptionOnInvalidArray(): void
    {
        $this->expectException(\TypeError::class);

        $transformer = $this->createTransformer();
        /* @phpstan-ignore argument.type */
        $transformer->transform([new \stdClass()]);
    }

    public function testReverseTransformNull(): void
    {
        $transformer = $this->createTransformer();
        static::assertSame([], $transformer->reverseTransform(null));
    }

    public function testReverseTransformEmptyString(): void
    {
        $transformer = $this->createTransformer();
        static::assertSame([], $transformer->reverseTransform('    '));
    }

    public function testReverseTransformException(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        $transformer = $this->createTransformer();
        /* @phpstan-ignore argument.type */
        static::assertSame([], $transformer->reverseTransform([]));
    }

    /**
     * @param non-empty-string $separator
     */
    private function createTransformer(string $separator = ','): ProductOptionValuesToStringTransformer
    {
        $repository = $this->createMock(ProductOptionValueRepository::class);
        $repository->method('createNew')
            ->willReturnCallback(static fn (...$args) => new TestProductOptionValue(...$args))
        ;

        return new ProductOptionValuesToStringTransformer($repository, $separator);
    }
}

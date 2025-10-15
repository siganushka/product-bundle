<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Model;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Model\CombinedOptionValueCollection;

class CombinedOptionValueCollectionTest extends TestCase
{
    public function testAll(): void
    {
        $collection = new CombinedOptionValueCollection();
        static::assertInstanceOf(Collection::class, $collection);
        static::assertCount(0, $collection);
        static::assertNull($collection->code);
        static::assertNull($collection->text);

        $foo = new ProductOptionValue('a', 'foo');
        $bar = new ProductOptionValue('b', 'bar');
        $baz = new ProductOptionValue('c', 'baz');

        $collection = new CombinedOptionValueCollection($foo, $bar, $baz);
        static::assertCount(3, $collection);
        static::assertSame('a-b-c', $collection->code);
        static::assertSame('foo/bar/baz', $collection->text);
        static::assertSame($foo, $collection[0]);
        static::assertSame($bar, $collection[1]);
        static::assertSame($baz, $collection[2]);

        $collection = new CombinedOptionValueCollection($baz, $bar, $foo);
        static::assertCount(3, $collection);
        static::assertSame('a-b-c', $collection->code);
        static::assertSame('baz/bar/foo', $collection->text);
        static::assertSame($baz, $collection[0]);
        static::assertSame($bar, $collection[1]);
        static::assertSame($foo, $collection[2]);
    }
}

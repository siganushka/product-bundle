<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;

class ProductOptionValueTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new TestProductOptionValue(name: 'foo');
        static::assertSame('foo', $entity->getName());

        $entity = new TestProductOptionValue(code: 'test', name: 'bar');
        static::assertSame('test', $entity->getCode());
        static::assertSame('bar', $entity->getName());
    }
}

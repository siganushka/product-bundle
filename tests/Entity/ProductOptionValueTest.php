<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;

class ProductOptionValueTest extends TestCase
{
    public function testAll(): void
    {
        $entity = new TestProductOptionValue(text: 'foo');
        static::assertSame('foo', $entity->getText());

        $entity = new TestProductOptionValue(code: 'test', text: 'bar');
        static::assertSame('test', $entity->getCode());
        static::assertSame('bar', $entity->getText());
    }
}

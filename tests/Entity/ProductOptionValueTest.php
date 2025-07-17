<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductOptionValueTest extends TestCase
{
    public function testCodeInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The code with value "a@b" contains illegal character(s)');

        new ProductOptionValue('a@b');
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\SiganushkaProductBundle;

final class SiganushkaProductBundleTest extends TestCase
{
    public function testGetPath(): void
    {
        static::assertSame(\dirname(__DIR__), (new SiganushkaProductBundle())->getPath());
    }
}

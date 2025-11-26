<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;

class RoutesTest extends TestCase
{
    public function testRotues(): void
    {
        $locator = new FileLocator(__DIR__.'/../config/');

        new LoaderResolver([
            $loader = new PhpFileLoader($locator),
            new AttributeDirectoryLoader($locator, new AttributeRouteControllerLoader()),
        ]);

        $routes = $loader->load('routes.php');
        static::assertSame([
            'siganushka_product_product_getcollection',
            'siganushka_product_product_postcollection',
            'siganushka_product_product_getitem',
            'siganushka_product_product_putitem',
            'siganushka_product_product_deleteitem',
            'siganushka_product_product_getvariants',
            'siganushka_product_product_putvariants',
            'siganushka_product_productoption_getitem',
            'siganushka_product_productoption_putitem',
        ], array_keys($routes->all()));
    }
}

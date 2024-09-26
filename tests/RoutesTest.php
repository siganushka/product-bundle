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
    /**
     * @dataProvider routesProvider
     */
    public function testRotues(string $routeName, string $path, array $methods): void
    {
        $locator = new FileLocator(__DIR__.'/../config/');

        new LoaderResolver([
            $loader = new PhpFileLoader($locator),
            new AttributeDirectoryLoader($locator, new AttributeRouteControllerLoader()),
        ]);

        $routes = $loader->load('routes.php');

        static::assertSame($path, $routes->get($routeName)?->getPath());
        static::assertSame($methods, $routes->get($routeName)?->getMethods());
    }

    public static function routesProvider(): iterable
    {
        yield ['siganushka_product_product_getcollection', '/products', ['GET']];
        yield ['siganushka_product_product_postcollection', '/products', ['POST']];
        yield ['siganushka_product_product_getitem', '/products/{id}', ['GET']];
        yield ['siganushka_product_product_putitem', '/products/{id}', ['PUT', 'PATCH']];
        yield ['siganushka_product_product_putitemvariants', '/products/{id}/variants', ['PUT', 'PATCH']];
        yield ['siganushka_product_product_deleteitem', '/products/{id}', ['DELETE']];
        yield ['siganushka_product_productvariant_getitem', '/product-variants/{id}', ['GET']];
        yield ['siganushka_product_productvariant_putitem', '/product-variants/{id}', ['PUT', 'PATCH']];
        yield ['siganushka_product_productvariant_deleteitem', '/product-variants/{id}', ['DELETE']];
    }
}

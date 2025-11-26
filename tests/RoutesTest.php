<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutesTest extends TestCase
{
    protected RouteCollection $routes;

    protected function setUp(): void
    {
        $locator = new FileLocator(__DIR__.'/../config/');

        new LoaderResolver([
            $loader = new PhpFileLoader($locator),
            new AttributeDirectoryLoader($locator, new AttributeRouteControllerLoader()),
        ]);

        $this->routes = $loader->load('routes.php');
    }

    public function testAll(): void
    {
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
        ], array_keys($this->routes->all()));
    }

    /**
     * @dataProvider routesProvider
     */
    public function testRotues(string $routeName, string $path, array $methods): void
    {
        /** @var Route */
        $route = $this->routes->get($routeName);

        static::assertSame($path, $route->getPath());
        static::assertSame($methods, $route->getMethods());
        static::assertTrue($route->getDefault('_stateless'));
    }

    public static function routesProvider(): iterable
    {
        yield ['siganushka_product_product_getcollection', '/products', ['GET']];
        yield ['siganushka_product_product_postcollection', '/products', ['POST']];
        yield ['siganushka_product_product_getitem', '/products/{id}', ['GET']];
        yield ['siganushka_product_product_putitem', '/products/{id}', ['PUT', 'PATCH']];
        yield ['siganushka_product_product_deleteitem', '/products/{id}', ['DELETE']];
        yield ['siganushka_product_product_getvariants', '/products/{id}/variants', ['GET']];
        yield ['siganushka_product_product_putvariants', '/products/{id}/variants', ['PUT', 'PATCH']];
        yield ['siganushka_product_productoption_getitem', '/product-options/{id}', ['GET']];
        yield ['siganushka_product_productoption_putitem', '/product-options/{id}', ['PUT', 'PATCH']];
    }
}

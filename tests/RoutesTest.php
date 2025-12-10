<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Controller\ProductController;
use Siganushka\ProductBundle\Controller\ProductOptionController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutesTest extends TestCase
{
    protected RouteCollection $routes;

    protected function setUp(): void
    {
        $loader = new PhpFileLoader(new FileLocator(__DIR__.'/../config/'));
        $this->routes = $loader->load('routes.php');
    }

    public function testAll(): void
    {
        $routes = iterator_to_array(self::routesProvider());
        $routeNames = array_map(fn (array $route) => $route[0], $routes);

        static::assertSame($routeNames, array_keys($this->routes->all()));
    }

    #[DataProvider('routesProvider')]
    public function testRotues(string $routeName, string $path, array $methods): void
    {
        /** @var Route */
        $route = $this->routes->get($routeName);

        static::assertSame($path, $route->getPath());
        static::assertSame($methods, $route->getMethods());
    }

    public static function routesProvider(): iterable
    {
        yield ['siganushka_product_getcollection', '/products', ['GET'], [ProductController::class, 'getCollection']];
        yield ['siganushka_product_postcollection', '/products', ['POST'], [ProductController::class, 'postCollection']];
        yield ['siganushka_product_getitem', '/products/{id}', ['GET'], [ProductController::class, 'getItem']];
        yield ['siganushka_product_putitem', '/products/{id}', ['PUT', 'PATCH'], [ProductController::class, 'putItem']];
        yield ['siganushka_product_deleteitem', '/products/{id}', ['DELETE'], [ProductController::class, 'deleteItem']];
        yield ['siganushka_product_getvariants', '/products/{id}/variants', ['GET'], [ProductController::class, 'getVariants']];
        yield ['siganushka_product_putvariants', '/products/{id}/variants', ['PUT', 'PATCH'], [ProductController::class, 'putVariants']];

        yield ['siganushka_productoption_getitem', '/product-options/{id}', ['GET'], [ProductOptionController::class, 'getItem']];
        yield ['siganushka_productoption_putitem', '/product-options/{id}', ['PUT', 'PATCH'], [ProductOptionController::class, 'putItem']];
    }
}

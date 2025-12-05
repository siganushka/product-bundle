<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Siganushka\ProductBundle\Controller\ProductController;
use Siganushka\ProductBundle\Controller\ProductOptionController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('siganushka_product_getcollection', '/products')
        ->controller([ProductController::class, 'getCollection'])
        ->methods(['GET'])
    ;

    $routes->add('siganushka_product_postcollection', '/products')
        ->controller([ProductController::class, 'postCollection'])
        ->methods(['POST'])
    ;

    $routes->add('siganushka_product_getitem', '/products/{id<\d+>}')
        ->controller([ProductController::class, 'getItem'])
        ->methods(['GET'])
    ;

    $routes->add('siganushka_product_putitem', '/products/{id<\d+>}')
        ->controller([ProductController::class, 'putItem'])
        ->methods(['PUT', 'PATCH'])
    ;

    $routes->add('siganushka_product_deleteitem', '/products/{id<\d+>}')
        ->controller([ProductController::class, 'deleteItem'])
        ->methods(['DELETE'])
    ;

    $routes->add('siganushka_product_getvariants', '/products/{id<\d+>}/variants')
        ->controller([ProductController::class, 'getVariants'])
        ->methods(['GET'])
    ;

    $routes->add('siganushka_product_putvariants', '/products/{id<\d+>}/variants')
        ->controller([ProductController::class, 'putVariants'])
        ->methods(['PUT', 'PATCH'])
    ;

    $routes->add('siganushka_productoption_getitem', '/product-options/{id<\d+>}')
        ->controller([ProductOptionController::class, 'getItem'])
        ->methods(['GET'])
    ;

    $routes->add('siganushka_productoption_putitem', '/product-options/{id<\d+>}')
        ->controller([ProductOptionController::class, 'putItem'])
        ->methods(['PUT', 'PATCH'])
    ;
};

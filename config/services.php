<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Siganushka\ProductBundle\SiganushkaProductBundle;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $ref = new \ReflectionClass(SiganushkaProductBundle::class);
    $services->load($ref->getNamespaceName().'\\', '../src/')
        ->exclude([
            '../src/DependencyInjection/',
            '../src/Dto/',
            '../src/Entity/',
            '../src/Event/',
            '../src/Model/',
            '../src/SiganushkaProductBundle.php',
        ]);
};

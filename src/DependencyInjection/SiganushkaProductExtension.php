<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\SiganushkaProductBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SiganushkaProductExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('siganushka_generic')) {
            $configs = $container->getExtensionConfig($this->getAlias());

            $configuration = new Configuration();
            $config = $this->processConfiguration($configuration, $configs);
        }

        if ($container->hasExtension('twig')) {
            $refl = new \ReflectionClass(SiganushkaProductBundle::class);
            $path = \dirname($refl->getFileName()).'/Resources/templates';

            $container->prependExtensionConfig('twig', ['paths' => [$path]]);
        }
    }
}

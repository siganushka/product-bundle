<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SiganushkaProductExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $repositoriesMapping = [
            'product_class' => ProductRepository::class,
            'product_option_class' => ProductOptionRepository::class,
            'product_option_value_class' => ProductOptionValueRepository::class,
            'product_variant_class' => ProductVariantRepository::class,
        ];

        foreach ($repositoriesMapping as $configName => $repositoryClass) {
            $repositoryDef = $container->findDefinition($repositoryClass);
            $repositoryDef->setArgument('$entityClass', $config[$configName]);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('siganushka_generic')) {
            return;
        }

        $configs = $container->getExtensionConfig($this->getAlias());

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }
}

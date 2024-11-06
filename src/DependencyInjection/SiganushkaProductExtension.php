<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\Form\Extension\MoneyTypeExtension;
use Siganushka\ProductBundle\Formatter\MoneyFormatter;
use Siganushka\ProductBundle\Twig\MoneyExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Twig\Environment;

class SiganushkaProductExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach (Configuration::$resourceMapping as $configName => [, $repositoryClass]) {
            $repositoryDef = $container->findDefinition($repositoryClass);
            $repositoryDef->setArgument('$entityClass', $config[$configName]);
        }

        if (!class_exists(Environment::class)) {
            $container->removeDefinition(MoneyExtension::class);
        }

        $moneyTypeExtensionDef = $container->findDefinition(MoneyTypeExtension::class);
        $moneyTypeExtensionDef->setArgument(0, $config['money_decimals']);
        $moneyTypeExtensionDef->setArgument(1, $config['money_divisor']);
        $moneyTypeExtensionDef->setArgument(2, $config['money_currency']);

        $moneyFormatterDef = $container->findDefinition(MoneyFormatter::class);
        $moneyFormatterDef->setArgument(0, [
            MoneyFormatter::DIVISOR => $config['money_divisor'],
            MoneyFormatter::DECIMALS => $config['money_decimals'],
            MoneyFormatter::DEC_POINT => $config['money_dec_point'],
            MoneyFormatter::THOUSANDS_SEP => $config['money_thousands_sep'],
        ]);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $mappingOverride = [];
        foreach (Configuration::$resourceMapping as $configName => [$entityClass]) {
            if ($config[$configName] !== $entityClass) {
                $mappingOverride[$entityClass] = $config[$configName];
            }
        }

        $container->prependExtensionConfig('siganushka_generic', [
            'doctrine' => ['mapping_override' => $mappingOverride],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Doctrine\ORM\Events;
use Siganushka\ProductBundle\Doctrine\ProductListener;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Validator\Constraints\Image;

class SiganushkaProductExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach (Configuration::$resourceMapping as $configName => [, $repositoryClass]) {
            $repository = $container->findDefinition($repositoryClass);
            $repository->setArgument('$entityClass', $config[$configName]);
        }

        $productListener = $container->findDefinition(ProductListener::class);
        $productListener->addTag('doctrine.event_listener', ['event' => Events::onFlush]);
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

        $container->prependExtensionConfig('siganushka_media', [
            'channels' => [
                'product_img' => [
                    'constraint' => Image::class,
                    'constraint_options' => [
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/webp'],
                        'maxSize' => '2M',
                        'maxRatio' => 1,
                        'minRatio' => 1,
                        'minWidth' => 100,
                    ],
                    'resize' => class_exists(\Imagick::class) ? 800 : false,
                    'optimize' => class_exists(OptimizerChainFactory::class) ? 85 : false,
                ],
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Doctrine\ORM\Events;
use Siganushka\ProductBundle\Doctrine\ProductVariantUpdateListener;
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

        foreach (Configuration::RESOURCE_MAPPING as $configName => [, $repositoryClass]) {
            $repositoryClass = $container->findDefinition($repositoryClass);
            $repositoryClass->setArgument('$entityClass', $config[$configName]);
        }

        $container->findDefinition(ProductVariantUpdateListener::class)
            ->addTag('doctrine.event_listener', ['event' => Events::onFlush])
        ;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = array_merge(...$configs);

        $resolveTargetEntities = [];
        foreach (Configuration::RESOURCE_MAPPING as $configName => [$interface]) {
            $resolveTargetEntities[$interface] = $config[$configName] ?? null;
        }

        if (\count($rte = array_filter($resolveTargetEntities))) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => ['resolve_target_entities' => $rte],
            ]);
        }

        $container->prependExtensionConfig('siganushka_media', [
            'rules' => [
                'product_img' => [
                    'constraint' => Image::class,
                    'constraint_options' => [
                        'mimeTypes' => ['image/*'],
                        'maxSize' => '2M',
                        'minWidth' => 100,
                        'minRatio' => 1,
                        'maxRatio' => 1,
                    ],
                    'resize' => class_exists(\Imagick::class) ? 1080 : false,
                    'optimize' => class_exists(OptimizerChainFactory::class) ? 85 : false,
                ],
            ],
        ]);

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['TwigBundle'])) {
            $container->prependExtensionConfig('twig', [
                'form_themes' => ['@SiganushkaProduct/form_theme.html.twig'],
            ]);
        }
    }
}

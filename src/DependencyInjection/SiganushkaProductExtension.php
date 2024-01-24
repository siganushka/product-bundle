<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\Doctrine\EventListener\OptionRemoveListener;
use Siganushka\ProductBundle\Doctrine\EventListener\OptionValueRemoveListener;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\OptionValue;
use Siganushka\ProductBundle\Serializer\Normalizer\ProductVariantChoiceNormalizer;
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

        $optionRemoveListenerDef = $container->findDefinition(OptionRemoveListener::class);
        $optionRemoveListenerDef->addTag('doctrine.orm.entity_listener', ['event' => 'preRemove', 'entity' => Option::class]);

        $optionValueRemoveListenerDef = $container->findDefinition(OptionValueRemoveListener::class);
        $optionValueRemoveListenerDef->addTag('doctrine.orm.entity_listener', ['event' => 'preRemove', 'entity' => OptionValue::class]);

        $productVariantChoiceNormalizerDef = $container->findDefinition(ProductVariantChoiceNormalizer::class);
        $productVariantChoiceNormalizerDef->addTag('serializer.normalizer');
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

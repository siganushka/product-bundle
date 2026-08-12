<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const RESOURCE_MAPPING = [
        'product_class' => [AbstractProduct::class, ProductRepository::class],
        'product_option_class' => [AbstractProductOption::class, ProductOptionRepository::class],
        'product_option_value_class' => [AbstractProductOptionValue::class, ProductOptionValueRepository::class],
        'product_variant_class' => [AbstractProductVariant::class, ProductVariantRepository::class],
    ];

    /**
     * @return TreeBuilder<'array'>
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('siganushka_product');
        $rootNode = $treeBuilder->getRootNode();

        foreach (self::RESOURCE_MAPPING as $configName => [$interface]) {
            $rootNode->children()
                ->scalarNode($configName)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => \is_string($v) && !is_subclass_of($v, $interface, true))
                        ->thenInvalid('The value must be instanceof '.$interface.', %s given.')
                    ->end()
                ->end()
            ;
        }

        return $treeBuilder;
    }
}

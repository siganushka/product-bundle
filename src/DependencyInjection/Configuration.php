<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('siganushka_product');
        /** @var ArrayNodeDefinition */
        $rootNode = $treeBuilder->getRootNode();

        $classMapping = [
            'product_class' => Product::class,
            'product_option_class' => ProductOption::class,
            'product_option_value_class' => ProductOptionValue::class,
            'product_variant_class' => ProductVariant::class,
        ];

        foreach ($classMapping as $configName => $classFqcn) {
            $rootNode
                ->children()
                    ->scalarNode($configName)
                        ->defaultValue($classFqcn)
                        ->validate()
                            ->ifTrue(function ($v) use ($classFqcn) {
                                if (!class_exists($v)) {
                                    return false;
                                }

                                return !is_subclass_of($v, $classFqcn);
                            })
                            ->thenInvalid('The %s class must extends "'.$classFqcn.'" for using the "'.$configName.'".')
                        ->end()
                    ->end()
            ;
        }

        return $treeBuilder;
    }
}

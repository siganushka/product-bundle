<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\DependencyInjection;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public static array $resourceMapping = [
        'product_class' => [Product::class, ProductRepository::class],
        'product_option_class' => [ProductOption::class, ProductOptionRepository::class],
        'product_option_value_class' => [ProductOptionValue::class, ProductOptionValueRepository::class],
        'product_variant_class' => [ProductVariant::class, ProductVariantRepository::class],
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('siganushka_product');
        /** @var ArrayNodeDefinition */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->children()
            ->scalarNode('money_currency')
                ->defaultValue('CNY')
            ->end()
            ->integerNode('money_divisor')
                ->defaultValue(100)
            ->end()
            ->integerNode('money_decimals')
                ->defaultValue(2)
            ->end()
            ->scalarNode('money_dec_point')
                ->defaultValue('.')
            ->end()
            ->scalarNode('money_thousands_sep')
                ->defaultValue(',')
            ->end()
        ;

        foreach (static::$resourceMapping as $configName => [$entityClass]) {
            $rootNode->children()
                ->scalarNode($configName)
                    ->defaultValue($entityClass)
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => \is_string($v) && !is_a($v, $entityClass, true))
                        ->thenInvalid('The value must be instanceof '.$entityClass.', %s given.')
                    ->end()
                ->end()
            ;
        }

        return $treeBuilder;
    }
}

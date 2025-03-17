<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\DependencyInjection\Configuration;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Tests\Fixtures\FooProduct;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\FooProductVariant;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    private ConfigurationInterface $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();

        static::assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        static::assertInstanceOf(TreeBuilder::class, $treeBuilder);

        $processedConfig = $this->processor->processConfiguration($this->configuration, []);
        static::assertSame($processedConfig, [
            'product_class' => Product::class,
            'product_option_class' => ProductOption::class,
            'product_option_value_class' => ProductOptionValue::class,
            'product_variant_class' => ProductVariant::class,
        ]);
    }

    public function testCustomConfig(): void
    {
        $config = [
            'product_class' => FooProduct::class,
            'product_option_class' => FooProductOption::class,
            'product_option_value_class' => FooProductOptionValue::class,
            'product_variant_class' => FooProductVariant::class,
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config]);
        static::assertSame($processedConfig, $config);
    }

    public function testProductClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', Product::class));

        $config = ['product_class' => \stdClass::class];
        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testProductOptionClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', ProductOption::class));

        $config = ['product_option_class' => \stdClass::class];
        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testProductOptionValueClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', ProductOptionValue::class));

        $config = ['product_option_value_class' => \stdClass::class];
        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testpProductVariantClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', ProductVariant::class));

        $config = ['product_variant_class' => \stdClass::class];
        $this->processor->processConfiguration($this->configuration, [$config]);
    }
}

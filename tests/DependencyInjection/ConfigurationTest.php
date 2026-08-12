<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\DependencyInjection\Configuration;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
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
        $config = [
            'product_class' => TestProduct::class,
            'product_option_class' => TestProductOption::class,
            'product_option_value_class' => TestProductOptionValue::class,
            'product_variant_class' => TestProductVariant::class,
        ];

        $processedConfig = $this->processor->processConfiguration($this->configuration, [$config]);
        static::assertSame($processedConfig, $config);
    }

    public function testProductClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', AbstractProduct::class));

        $config = [
            'product_class' => \stdClass::class,
            'product_option_class' => TestProductOption::class,
            'product_option_value_class' => TestProductOptionValue::class,
            'product_variant_class' => TestProductVariant::class,
        ];

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testProductOptionClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', AbstractProductOption::class));

        $config = [
            'product_class' => TestProduct::class,
            'product_option_class' => \stdClass::class,
            'product_option_value_class' => TestProductOptionValue::class,
            'product_variant_class' => TestProductVariant::class,
        ];

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testProductOptionValueClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', AbstractProductOptionValue::class));

        $config = [
            'product_class' => TestProduct::class,
            'product_option_class' => TestProductOption::class,
            'product_option_value_class' => \stdClass::class,
            'product_variant_class' => TestProductVariant::class,
        ];

        $this->processor->processConfiguration($this->configuration, [$config]);
    }

    public function testpProductVariantClassInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(\sprintf('The value must be instanceof %s, "stdClass" given.', AbstractProductVariant::class));

        $config = [
            'product_class' => TestProduct::class,
            'product_option_class' => TestProductOption::class,
            'product_option_value_class' => TestProductOptionValue::class,
            'product_variant_class' => \stdClass::class,
        ];

        $this->processor->processConfiguration($this->configuration, [$config]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\DependencyInjection;

use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\DependencyInjection\SiganushkaProductExtension;
use Siganushka\ProductBundle\Doctrine\ProductPriceUpdateListener;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Form\ProductType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\Constraints\Image;

class SiganushkaProductExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        (new SiganushkaProductExtension())->load([self::createConfig()], $container);

        static::assertTrue($container->hasDefinition(ProductType::class));
        static::assertSame(TestProduct::class, $container->getDefinition(ProductRepository::class)->getArgument('$entityClass'));
        static::assertSame(TestProductOption::class, $container->getDefinition(ProductOptionRepository::class)->getArgument('$entityClass'));
        static::assertSame(TestProductOptionValue::class, $container->getDefinition(ProductOptionValueRepository::class)->getArgument('$entityClass'));
        static::assertSame(TestProductVariant::class, $container->getDefinition(ProductVariantRepository::class)->getArgument('$entityClass'));

        $listener = $container->getDefinition(ProductPriceUpdateListener::class);
        static::assertSame([['event' => Events::onFlush]], $listener->getTag('doctrine.event_listener'));
    }

    public function testPrepend(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig('siganushka_product', self::createConfig());

        $extension = new SiganushkaProductExtension();
        $extension->prepend($container);

        $doctrineConfigs = $container->getExtensionConfig('doctrine');
        static::assertCount(1, $doctrineConfigs);

        $orm = $doctrineConfigs[0]['orm'] ?? null;
        static::assertIsArray($orm);
        static::assertSame([
            AbstractProduct::class => TestProduct::class,
            AbstractProductOption::class => TestProductOption::class,
            AbstractProductOptionValue::class => TestProductOptionValue::class,
            AbstractProductVariant::class => TestProductVariant::class,
        ], $orm['resolve_target_entities']);

        $mediaConfigs = $container->getExtensionConfig('siganushka_media');
        static::assertCount(1, $mediaConfigs);

        $rules = $mediaConfigs[0]['rules'] ?? null;
        static::assertIsArray($rules);

        $rule = $rules['product_img'] ?? null;
        static::assertIsArray($rule);
        static::assertSame(Image::class, $rule['constraint']);

        $constraintOptions = $rule['constraint_options'] ?? null;
        static::assertIsArray($constraintOptions);
        static::assertSame(['image/*'], $constraintOptions['mimeTypes']);
        static::assertArrayHasKey('resize', $rule);
        static::assertArrayHasKey('optimize', $rule);
    }

    public function testPrependWithPartialConfig(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig('siganushka_product', [
            'product_class' => TestProduct::class,
        ]);

        (new SiganushkaProductExtension())->prepend($container);

        $doctrineConfigs = $container->getExtensionConfig('doctrine');
        static::assertCount(1, $doctrineConfigs);

        $orm = $doctrineConfigs[0]['orm'] ?? null;
        static::assertIsArray($orm);
        static::assertSame([
            AbstractProduct::class => TestProduct::class,
        ], $orm['resolve_target_entities']);
    }

    public function testPrependWithoutConfig(): void
    {
        $container = new ContainerBuilder();

        (new SiganushkaProductExtension())->prepend($container);

        static::assertSame([], $container->getExtensionConfig('doctrine'));
        static::assertCount(1, $container->getExtensionConfig('siganushka_media'));
    }

    private static function createConfig(): array
    {
        return [
            'product_class' => TestProduct::class,
            'product_option_class' => TestProductOption::class,
            'product_option_value_class' => TestProductOptionValue::class,
            'product_variant_class' => TestProductVariant::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form;

use Siganushka\MediaBundle\Form\DataTransformer\MediaToReferenceTransformer;
use Siganushka\MediaBundle\Form\Extension\RuleTypeExtension;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\MediaBundle\Rule;
use Siganushka\MediaBundle\RuleRegistry;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Siganushka\ProductBundle\Form\ProductOptionType;
use Siganushka\ProductBundle\Form\ProductType;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Siganushka\ProductBundle\Form\Type\ProductOptionCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Siganushka\ProductBundle\Form\Type\ProductVariantCollectionType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductTypeTest extends TypeTestCase
{
    public function testBuildForm(): void
    {
        $form = $this->factory->create(ProductType::class);

        static::assertSame(
            ['img', 'name', 'summary', 'options', 'variants', 'minLimit', 'maxLimit'],
            array_keys($form->createView()->children)
        );

        static::assertInstanceOf(TestProduct::class, $form->getData());
        /** @var \Doctrine\Common\Collections\Collection<int, TestProductVariant> $variants */
        $variants = $form->get('variants')->getData();
        static::assertCount(1, $variants);

        $variant = $variants->first();
        static::assertInstanceOf(TestProductVariant::class, $variant);
        static::assertSame('default', $variant->getCode());
        static::assertTrue($variant->isEnabled());
    }

    public function testSubmitWithoutOptions(): void
    {
        $form = $this->factory->create(ProductType::class);
        $form->submit([
            'img' => 'media-hash',
            'name' => 'T-Shirt',
            'summary' => 'A simple T-Shirt.',
            'options' => [],
            // 无 options 时 variants 只有一条默认记录，且 enabled 字段不存在，
            // ProductType 会主动丢弃多余的 enabled 输入，避免 extra_fields 校验错误
            'variants' => [['price' => '100', 'stock' => '5', 'enabled' => '1']],
            'minLimit' => '1',
            'maxLimit' => '10',
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());

        /** @var TestProduct $product */
        $product = $form->getData();
        static::assertSame('T-Shirt', $product->getName());
        static::assertSame('A simple T-Shirt.', $product->getSummary());
        static::assertSame(1, $product->getMinLimit());
        static::assertSame(10, $product->getMaxLimit());
        static::assertInstanceOf(TestMedia::class, $product->getImg());
        static::assertSame('media-hash', $product->getImg()->getHash());

        static::assertCount(1, $product->getVariants());

        /** @var TestProductVariant $variant */
        $variant = $product->getVariants()->first();
        static::assertSame('default', $variant->getCode());
        static::assertSame(100, $variant->getPrice());
        static::assertSame(5, $variant->getStock());
        static::assertTrue($variant->isEnabled());
        static::assertSame($product, $variant->getProduct());
    }

    public function testSubmitWithOptionsRebuildsVariants(): void
    {
        $product = $this->createProductWithColorOption();
        $form = $this->factory->create(ProductType::class, $product);

        // 创建表单时已按「颜色」生成 2 条 SKU
        static::assertCount(2, $product->getVariants());
        $previousVariants = $product->getVariants()->toArray();

        $form->submit([
            'img' => 'media-hash',
            'name' => 'T-Shirt',
            'options' => [['name' => 'Color', 'values' => 'Red,Blue']],
            'variants' => [
                ['price' => '100', 'stock' => '10', 'enabled' => '1'],
                ['price' => '200', 'stock' => '20', 'enabled' => '1'],
            ],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());

        /** @var TestProduct $product */
        $product = $form->getData();
        static::assertCount(2, $product->getVariants());

        // 重新索引，键必须从 0 连续递增
        static::assertSame([0, 1], array_keys($product->getVariants()->toArray()));
        static::assertSame(['red', 'blue'], array_map(
            static fn (TestProductVariant $variant) => $variant->getCode(),
            $product->getVariants()->toArray()
        ));

        $currentVariants = $product->getVariants()->toArray();

        // 相同 code 的 SKU 复用原对象，而不是重新创建
        static::assertSame(spl_object_id($previousVariants[0]), spl_object_id($currentVariants[0]));
        static::assertSame(spl_object_id($previousVariants[1]), spl_object_id($currentVariants[1]));

        static::assertSame([100, 200], array_map(
            static fn (TestProductVariant $variant) => $variant->getPrice(),
            $currentVariants
        ));
        static::assertTrue($currentVariants[0]->isEnabled());
        static::assertTrue($currentVariants[1]->isEnabled());
    }

    public function testSubmitWithChangedOptionsCreatesAndRemovesVariants(): void
    {
        $product = $this->createProductWithColorOption();
        $form = $this->factory->create(ProductType::class, $product);
        $previousVariants = $product->getVariants()->toArray();

        $form->submit([
            'img' => 'media-hash',
            'name' => 'T-Shirt',
            'options' => [['name' => 'Color', 'values' => 'Red,Blue,Green']],
            'variants' => [
                ['price' => '100', 'stock' => '10', 'enabled' => '1'],
                ['price' => '200', 'stock' => '20', 'enabled' => '1'],
                ['price' => '300', 'stock' => '30', 'enabled' => '1'],
                // 非法键会被 ProductType 主动移除
                ['price' => '999', 'stock' => '99', 'enabled' => '1'],
            ],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());

        /** @var TestProduct $product */
        $product = $form->getData();
        static::assertCount(3, $product->getVariants());
        static::assertSame([0, 1, 2], array_keys($product->getVariants()->toArray()));

        $currentVariants = $product->getVariants()->toArray();
        static::assertSame(['Red', 'Blue', 'Green'], array_map(
            static fn (TestProductVariant $variant) => $variant->getName(),
            $currentVariants
        ));
        static::assertSame([100, 200, 300], array_map(
            static fn (TestProductVariant $variant) => $variant->getPrice(),
            $currentVariants
        ));

        // 已有的 Red / Blue 复用，新增的 Green 创建新对象
        static::assertSame(spl_object_id($previousVariants[0]), spl_object_id($currentVariants[0]));
        static::assertSame(spl_object_id($previousVariants[1]), spl_object_id($currentVariants[1]));
        static::assertNotContains(
            spl_object_id($currentVariants[2]),
            array_map(spl_object_id(...), $previousVariants)
        );
    }

    public function testSubmitWithoutRequiredFieldsIsInvalid(): void
    {
        $form = $this->factory->create(ProductType::class);
        $form->submit([
            'img' => null,
            'name' => null,
            'options' => [],
            'variants' => [
                ['price' => null, 'stock' => null],
            ],
        ]);

        static::assertFalse($form->isValid());
        static::assertGreaterThan(0, $form->get('img')->getErrors()->count());
        static::assertGreaterThan(0, $form->get('name')->getErrors()->count());
    }

    public function testSubmitWithoutEnabledVariantsIsInvalid(): void
    {
        $product = $this->createProductWithColorOption();
        $form = $this->factory->create(ProductType::class, $product);

        $form->submit([
            'img' => 'media-hash',
            'name' => 'T-Shirt',
            'options' => [['name' => 'Color', 'values' => 'Red,Blue']],
            // 未提交 enabled，复选框默认为 false
            'variants' => [
                ['price' => '100', 'stock' => '10'],
                ['price' => '200', 'stock' => '20'],
            ],
        ]);

        static::assertFalse($form->isValid());

        $messages = array_map(
            static fn (FormError $error) => $error->getMessage(),
            iterator_to_array($form->get('variants')->getErrors())
        );

        static::assertContains('At least one product variants has to be enabled.', $messages);
    }

    public function testSubmitWithMalformedVariantsPayload(): void
    {
        $form = $this->factory->create(ProductType::class);
        $form->submit([
            'img' => 'media-hash',
            'name' => 'T-Shirt',
            'options' => [],
            // variants 非数组时跳过过滤逻辑，交给 CollectionType 产生转换失败
            'variants' => 'invalid',
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertFalse($form->get('variants')->isSynchronized());
        static::assertFalse($form->isValid());
    }

    public function testSubmitWithMalformedVariantItem(): void
    {
        $product = $this->createProductWithColorOption();
        $form = $this->factory->create(ProductType::class, $product);

        $form->submit([
            'img' => 'media-hash',
            'name' => 'T-Shirt',
            'options' => [['name' => 'Color', 'values' => 'Red,Blue']],
            'variants' => [
                ['price' => '100', 'stock' => '10', 'enabled' => '1'],
                // 单行非数组时保留原样，由 CollectionType 产生转换失败
                'invalid',
            ],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertFalse($form->get('variants')->get('1')->isSynchronized());
        static::assertFalse($form->isValid());
    }

    protected function getTypes(): array
    {
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->method('getClassName')->willReturn(TestProduct::class);
        $productRepository->method('createNew')->willReturnCallback(
            static fn (...$args) => new TestProduct(...$args)
        );

        $variantRepository = $this->createMock(ProductVariantRepository::class);
        $variantRepository->method('getClassName')->willReturn(TestProductVariant::class);
        $variantRepository->method('createNew')->willReturnCallback(
            static fn (...$args) => new TestProductVariant(...$args)
        );

        $optionRepository = $this->createMock(ProductOptionRepository::class);
        $optionRepository->method('getClassName')->willReturn(TestProductOption::class);
        $optionRepository->method('createNew')->willReturnCallback(
            static fn (...$args) => new TestProductOption(...$args)
        );

        $optionValueRepository = $this->createMock(ProductOptionValueRepository::class);
        $optionValueRepository->method('getClassName')->willReturn(TestProductOptionValue::class);
        $optionValueRepository->method('createNew')->willReturnCallback(
            static fn (...$args) => new TestProductOptionValue(...$args)
        );

        $mediaTransformer = $this->createMock(MediaToReferenceTransformer::class);
        $mediaTransformer->method('transform')->willReturnCallback(
            static fn (?TestMedia $media) => $media?->getHash()
        );
        $mediaTransformer->method('reverseTransform')->willReturnCallback(
            static fn (?string $hash) => null !== $hash && '' !== $hash ? (new TestMedia())->setHash($hash) : null
        );

        return [
            new ProductType($productRepository, $variantRepository),
            new ProductOptionCollectionType(),
            new ProductOptionType($optionRepository),
            new ProductOptionValuesTextType(new ProductOptionValuesToStringTransformer($optionValueRepository)),
            new ProductVariantCollectionType(),
            new ProductVariantType($variantRepository),
            new MediaType($mediaTransformer),
        ];
    }

    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    protected function getTypeExtensions(): array
    {
        $registry = $this->createMock(RuleRegistry::class);
        $registry->method('get')
            ->willReturnCallback(static fn (string $alias) => new Rule($alias))
        ;

        return [
            new RuleTypeExtension($registry),
        ];
    }

    private function createProductWithColorOption(): TestProduct
    {
        $po = new TestProductOption('Color');
        $po->addValue(new TestProductOptionValue('red', 'Red'));
        $po->addValue(new TestProductOptionValue('blue', 'Blue'));

        $product = new TestProduct('T-Shirt');
        $product->addOption($po);

        return $product;
    }
}

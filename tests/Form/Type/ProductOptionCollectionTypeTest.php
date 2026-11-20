<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\Type;

use Siganushka\MediaBundle\Form\DataTransformer\MediaToReferenceTransformer;
use Siganushka\MediaBundle\Form\Extension\RuleTypeExtension;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\MediaBundle\Rule;
use Siganushka\MediaBundle\RuleRegistry;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Siganushka\ProductBundle\Form\ProductOptionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductOptionCollectionTypeTest extends TypeTestCase
{
    public function testConfigureOptions(): void
    {
        $form = $this->factory->create(ProductOptionCollectionType::class, []);

        static::assertSame(ProductOptionType::class, $form->getConfig()->getOption('entry_type'));
        static::assertTrue($form->getConfig()->getOption('allow_add'));
        static::assertTrue($form->getConfig()->getOption('allow_delete'));
        static::assertFalse($form->getConfig()->getOption('by_reference'));
        static::assertFalse($form->getConfig()->getOption('error_bubbling'));
    }

    public function testSubmit(): void
    {
        $form = $this->factory->create(ProductOptionCollectionType::class, []);
        $form->submit([
            ['name' => 'Color', 'values' => 'Red,Blue'],
            ['name' => 'Size', 'values' => 'S,M'],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());

        /** @var array<int, TestProductOption> $options */
        $options = $form->getData();
        static::assertCount(2, $options);
        static::assertSame(['Color', 'Size'], array_map(
            static fn (TestProductOption $option) => $option->getName(),
            $options
        ));
        static::assertSame(['Red', 'Blue'], array_map(
            static fn (TestProductOptionValue $value) => $value->getName(),
            $options[0]->getValues()->toArray()
        ));
    }

    public function testSubmitWithDeletion(): void
    {
        $data = [
            (new TestProductOption('Color'))->addValue(new TestProductOptionValue('red', 'Red')),
            (new TestProductOption('Size'))->addValue(new TestProductOptionValue('s', 'S')),
        ];

        $form = $this->factory->create(ProductOptionCollectionType::class, $data);
        static::assertCount(2, $form->createView()->children);

        $form->submit([
            ['name' => 'Color', 'values' => 'Red,Blue'],
        ]);

        static::assertTrue($form->isSynchronized());

        /** @var array<int, TestProductOption> $options */
        $options = $form->getData();
        static::assertCount(1, $options);
        static::assertSame('Color', $options[0]->getName());
    }

    protected function getTypes(): array
    {
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
            new ProductOptionCollectionType(),
            new ProductOptionType($optionRepository),
            new ProductOptionValuesTextType(new ProductOptionValuesToStringTransformer($optionValueRepository)),
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
}

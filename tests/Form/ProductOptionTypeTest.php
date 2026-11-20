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
use Siganushka\ProductBundle\Form\ProductOptionValueType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductOptionTypeTest extends TypeTestCase
{
    public function testBuildFormWithSimpleValues(): void
    {
        $form = $this->factory->create(ProductOptionType::class, new TestProductOption(), ['simple' => true]);

        static::assertSame(['name', 'values'], array_keys($form->createView()->children));
    }

    public function testSubmitWithSimpleValues(): void
    {
        $option = new TestProductOption();
        $form = $this->factory->create(ProductOptionType::class, $option, ['simple' => true]);
        $form->submit(['name' => 'Color', 'values' => 'Red,Blue']);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());
        static::assertSame('Color', $option->getName());
        static::assertSame(['Red', 'Blue'], array_map(
            static fn (TestProductOptionValue $value) => $value->getName(),
            $option->getValues()->toArray()
        ));
    }

    public function testSubmitWithCollectionValues(): void
    {
        $option = new TestProductOption();
        $form = $this->factory->create(ProductOptionType::class, $option);
        $form->submit([
            'name' => 'Color',
            'values' => [
                ['img' => null, 'name' => 'Red'],
                ['img' => 'media-hash', 'name' => 'Blue'],
            ],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());
        static::assertCount(2, $option->getValues());

        static::assertSame(['Red', 'Blue'], array_map(
            static fn (TestProductOptionValue $value) => $value->getName(),
            $option->getValues()->toArray()
        ));
        static::assertSame('media-hash', $option->getValues()->toArray()[1]->getImg()?->getHash());
    }

    public function testSubmitWithoutNameAndValuesIsInvalid(): void
    {
        $form = $this->factory->create(ProductOptionType::class, new TestProductOption(), ['simple' => true]);
        $form->submit(['name' => null, 'values' => '']);

        static::assertFalse($form->isValid());
        static::assertGreaterThan(0, $form->get('name')->getErrors()->count());
        static::assertGreaterThan(0, $form->get('values')->getErrors()->count());
    }

    public function testSubmitWithDuplicatedValuesIsInvalid(): void
    {
        $form = $this->factory->create(ProductOptionType::class, new TestProductOption(), ['simple' => true]);
        $form->submit(['name' => 'Color', 'values' => 'Red,Red']);

        static::assertFalse($form->isValid());
        static::assertGreaterThan(0, $form->get('values')->getErrors()->count());
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
            new ProductOptionType($optionRepository),
            new ProductOptionValuesTextType(new ProductOptionValuesToStringTransformer($optionValueRepository)),
            new ProductOptionValuesCollectionType(),
            new ProductOptionValueType($optionValueRepository),
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

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form;

use Siganushka\MediaBundle\Form\DataTransformer\MediaToReferenceTransformer;
use Siganushka\MediaBundle\Form\Extension\RuleTypeExtension;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\MediaBundle\Rule;
use Siganushka\MediaBundle\RuleRegistry;
use Siganushka\ProductBundle\Form\ProductOptionValueType;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductOptionValueTypeTest extends TypeTestCase
{
    public function testBuildForm(): void
    {
        $form = $this->factory->create(ProductOptionValueType::class, new TestProductOptionValue());

        static::assertSame(['img', 'name'], array_keys($form->createView()->children));
        static::assertFalse($form->get('img')->getConfig()->getOption('required'));
        static::assertTrue($form->get('name')->getConfig()->getOption('required'));
    }

    public function testBuildFormWithHiddenLabel(): void
    {
        $form = $this->factory->create(ProductOptionValueType::class, new TestProductOptionValue(), [
            'label' => false,
        ]);

        static::assertSame('width: 38px; height: 38px', $form->get('img')->getConfig()->getOption('style'));
        static::assertSame(['class' => 'w-0'], $form->get('img')->getConfig()->getOption('row_attr'));
    }

    public function testSubmit(): void
    {
        $value = new TestProductOptionValue();
        $form = $this->factory->create(ProductOptionValueType::class, $value);
        $form->submit(['img' => 'media-hash', 'name' => 'Red']);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());
        static::assertSame('Red', $value->getName());
        static::assertInstanceOf(TestMedia::class, $value->getImg());
        static::assertSame('media-hash', $value->getImg()->getHash());
    }

    public function testSubmitWithoutNameIsInvalid(): void
    {
        $form = $this->factory->create(ProductOptionValueType::class, new TestProductOptionValue());
        $form->submit(['img' => null, 'name' => null]);

        static::assertFalse($form->isValid());
        static::assertGreaterThan(0, $form->get('name')->getErrors()->count());
    }

    public function testSubmitWithSeparatorInNameIsInvalid(): void
    {
        $form = $this->factory->create(ProductOptionValueType::class, new TestProductOptionValue());
        $form->submit(['img' => null, 'name' => 'Red,Blue']);

        static::assertFalse($form->isValid());

        $messages = array_map(
            static fn (FormError $error) => $error->getMessage(),
            iterator_to_array($form->get('name')->getErrors())
        );

        static::assertContains('This value cannot contain special characters.', $messages);
    }

    protected function getTypes(): array
    {
        $repository = $this->createMock(ProductOptionValueRepository::class);
        $repository->method('getClassName')->willReturn(TestProductOptionValue::class);

        $mediaTransformer = $this->createMock(MediaToReferenceTransformer::class);
        $mediaTransformer->method('transform')->willReturnCallback(
            static fn (?TestMedia $media) => $media?->getHash()
        );
        $mediaTransformer->method('reverseTransform')->willReturnCallback(
            static fn (?string $hash) => null !== $hash && '' !== $hash ? (new TestMedia())->setHash($hash) : null
        );

        return [
            new ProductOptionValueType($repository),
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

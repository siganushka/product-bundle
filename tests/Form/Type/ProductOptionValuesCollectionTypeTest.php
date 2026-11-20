<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\Type;

use Siganushka\MediaBundle\Form\DataTransformer\MediaToReferenceTransformer;
use Siganushka\MediaBundle\Form\Extension\RuleTypeExtension;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\MediaBundle\Rule;
use Siganushka\MediaBundle\RuleRegistry;
use Siganushka\ProductBundle\Form\ProductOptionValueType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesCollectionType;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestMedia;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductOptionValuesCollectionTypeTest extends TypeTestCase
{
    public function testConfigureOptions(): void
    {
        $form = $this->factory->create(ProductOptionValuesCollectionType::class, []);

        static::assertSame(ProductOptionValueType::class, $form->getConfig()->getOption('entry_type'));
        static::assertTrue($form->getConfig()->getOption('allow_add'));
        static::assertTrue($form->getConfig()->getOption('allow_delete'));
        static::assertFalse($form->getConfig()->getOption('by_reference'));
        static::assertFalse($form->getConfig()->getOption('error_bubbling'));
        static::assertSame('__PRODUCT_OPTION_VALUES__', $form->getConfig()->getOption('prototype_name'));
    }

    public function testSubmit(): void
    {
        $form = $this->factory->create(ProductOptionValuesCollectionType::class, []);
        $form->submit([
            ['img' => null, 'name' => 'Red'],
            ['img' => 'media-hash', 'name' => 'Blue'],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());

        /** @var array<int, TestProductOptionValue> $values */
        $values = $form->getData();
        static::assertCount(2, $values);
        static::assertSame(['Red', 'Blue'], array_map(
            static fn (TestProductOptionValue $value) => $value->getName(),
            $values
        ));
        static::assertSame('media-hash', $values[1]->getImg()?->getHash());
    }

    public function testSubmitWithoutNameIsInvalid(): void
    {
        $form = $this->factory->create(ProductOptionValuesCollectionType::class, []);
        $form->submit([
            ['img' => null, 'name' => null],
        ]);

        static::assertFalse($form->isValid());
        static::assertGreaterThan(0, $form->get('0')->get('name')->getErrors()->count());
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
            new ProductOptionValuesCollectionType(),
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

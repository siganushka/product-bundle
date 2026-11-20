<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form;

use Siganushka\ProductBundle\Form\ProductVariantType;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductVariantTypeTest extends TypeTestCase
{
    public function testBuildFormWithoutOptionValues(): void
    {
        $form = $this->factory->create(ProductVariantType::class, new TestProductVariant());

        static::assertSame(['price', 'stock'], array_keys($form->createView()->children));
        static::assertTrue($form->get('price')->getConfig()->getOption('required'));
        static::assertFalse($form->get('stock')->getConfig()->getOption('required'));
    }

    public function testBuildFormWithoutData(): void
    {
        $form = $this->factory->create(ProductVariantType::class);

        static::assertSame(['price', 'stock'], array_keys($form->createView()->children));
        static::assertNull($form->getData());
    }

    public function testBuildFormWithOptionValues(): void
    {
        $form = $this->factory->create(ProductVariantType::class, new TestProductVariant($this->createChoice()));

        static::assertSame(['name', 'price', 'stock', 'enabled'], array_keys($form->createView()->children));
        static::assertTrue($form->get('name')->getConfig()->getOption('disabled'));
        static::assertSame('generic.enable', $form->get('enabled')->getConfig()->getOption('label'));
    }

    public function testBuildFormWithOptionValuesAndHiddenLabel(): void
    {
        $form = $this->factory->create(ProductVariantType::class, new TestProductVariant($this->createChoice()), [
            'label' => false,
        ]);

        static::assertSame(['enabled', 'name', 'price', 'stock'], array_keys($form->createView()->children));
        static::assertFalse($form->get('price')->getConfig()->getOption('required'));
        static::assertFalse($form->get('enabled')->getConfig()->getOption('label'));
    }

    public function testSubmit(): void
    {
        $variant = new TestProductVariant($this->createChoice());
        $form = $this->factory->create(ProductVariantType::class, $variant);
        $form->submit(['price' => '12', 'stock' => '3', 'enabled' => '1']);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());
        static::assertSame(12, $variant->getPrice());
        static::assertSame(3, $variant->getStock());
        static::assertTrue($variant->isEnabled());
        static::assertSame('Red', $variant->getName());
    }

    public function testSubmitEnabledVariantWithoutPriceIsInvalid(): void
    {
        $variant = new TestProductVariant($this->createChoice());
        $form = $this->factory->create(ProductVariantType::class, $variant);
        $form->submit(['price' => null, 'stock' => '3', 'enabled' => '1']);

        static::assertFalse($form->isValid());
        static::assertGreaterThan(0, $form->get('price')->getErrors()->count());
    }

    public function testSubmitDisabledVariantWithoutPriceIsValid(): void
    {
        $variant = new TestProductVariant($this->createChoice());
        $form = $this->factory->create(ProductVariantType::class, $variant);
        $form->submit(['price' => null, 'stock' => '3']);

        static::assertTrue($form->isValid());
        static::assertFalse($variant->isEnabled());
    }

    protected function getTypes(): array
    {
        $repository = $this->createMock(ProductVariantRepository::class);
        $repository->method('getClassName')->willReturn(TestProductVariant::class);

        return [
            new ProductVariantType($repository),
        ];
    }

    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    private function createChoice(): ProductVariantChoice
    {
        return ProductVariantChoice::create(new TestProductOptionValue('red', 'Red'));
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Siganushka\ProductBundle\Form\Type\ProductVariantCollectionType;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ProductVariantCollectionTypeTest extends TypeTestCase
{
    public function testConfigureOptions(): void
    {
        $form = $this->factory->create(ProductVariantCollectionType::class, new ArrayCollection());

        static::assertSame(ProductVariantType::class, $form->getConfig()->getOption('entry_type'));
        static::assertFalse($form->getConfig()->getOption('allow_add'));
        static::assertFalse($form->getConfig()->getOption('allow_delete'));
        static::assertFalse($form->getConfig()->getOption('by_reference'));
        static::assertFalse($form->getConfig()->getOption('error_bubbling'));
    }

    public function testSubmit(): void
    {
        $variants = $this->createVariants();
        $form = $this->factory->create(ProductVariantCollectionType::class, $variants);
        $form->submit([
            ['price' => '100', 'stock' => '10', 'enabled' => '1'],
            ['price' => '200', 'stock' => '20', 'enabled' => '1'],
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isValid());

        $items = $variants->toArray();
        static::assertSame([100, 200], array_map(
            static fn (TestProductVariant $variant) => $variant->getPrice(),
            $items
        ));
        static::assertSame([10, 20], array_map(
            static fn (TestProductVariant $variant) => $variant->getStock(),
            $items
        ));
        static::assertTrue($items[0]->isEnabled());
        static::assertTrue($items[1]->isEnabled());
    }

    public function testSubmitWithAtLeastOneEnabledVariant(): void
    {
        $variants = $this->createVariants();
        $items = $variants->toArray();
        $items[0]->setEnabled(false);

        $form = $this->factory->create(ProductVariantCollectionType::class, $variants);
        $form->submit([
            ['price' => '100', 'stock' => '10'],
            ['price' => '200', 'stock' => '20', 'enabled' => '1'],
        ]);

        static::assertTrue($form->isValid());
        static::assertFalse($items[0]->isEnabled());
        static::assertTrue($items[1]->isEnabled());
    }

    public function testSubmitWithoutEnabledVariantsIsInvalid(): void
    {
        $variants = $this->createVariants();
        $form = $this->factory->create(ProductVariantCollectionType::class, $variants);
        $form->submit([
            ['price' => '100', 'stock' => '10'],
            ['price' => '200', 'stock' => '20'],
        ]);

        static::assertFalse($form->isValid());

        $messages = array_map(
            static fn (FormError $error) => $error->getMessage(),
            iterator_to_array($form->getErrors())
        );

        static::assertContains('At least one product variants has to be enabled.', $messages);
    }

    protected function getTypes(): array
    {
        $repository = $this->createMock(ProductVariantRepository::class);
        $repository->method('getClassName')->willReturn(TestProductVariant::class);

        return [
            new ProductVariantCollectionType(),
            new ProductVariantType($repository),
        ];
    }

    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @return ArrayCollection<int, TestProductVariant>
     */
    private function createVariants(): ArrayCollection
    {
        $red = new TestProductVariant(ProductVariantChoice::create(new TestProductOptionValue('red', 'Red')));
        $blue = new TestProductVariant(ProductVariantChoice::create(new TestProductOptionValue('blue', 'Blue')));

        return new ArrayCollection([$red, $blue]);
    }
}

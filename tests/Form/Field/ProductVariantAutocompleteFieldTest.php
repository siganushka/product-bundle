<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\Field;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Form\Field\ProductVariantAutocompleteField;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceLabel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

class ProductVariantAutocompleteFieldTest extends TestCase
{
    public function testConfigureOptions(): void
    {
        $resolver = $this->createOptionsResolver();
        $options = $resolver->resolve();

        static::assertSame(TestProductVariant::class, $options['class']);
        static::assertSame(20, $options['max_results']);
        static::assertSame(['product.name', 'name'], $options['searchable_fields']);
        static::assertSame(['maxOptions' => 100], $options['tom_select_options']);
    }

    public function testGetParent(): void
    {
        $type = new ProductVariantAutocompleteField($this->createMock(ProductVariantRepository::class));

        static::assertSame(BaseEntityAutocompleteType::class, $type->getParent());
    }

    public function testChoiceLabel(): void
    {
        $options = $this->createOptionsResolver()->resolve();

        $choiceLabel = $options['choice_label'];
        static::assertInstanceOf(ChoiceLabel::class, $choiceLabel);

        /** @var \Closure(AbstractProductVariant): ?string $label */
        $label = $choiceLabel->getOption();

        $product = new TestProduct('T-Shirt');

        $red = new TestProductVariant(ProductVariantChoice::create(new TestProductOptionValue('red', 'Red')));
        $product->addVariant($red);
        static::assertSame('T-Shirt [Red]', $label($red));

        $default = new TestProductVariant();
        $product->addVariant($default);
        static::assertSame('T-Shirt', $label($default));

        static::assertNull($label(new TestProductVariant()));
    }

    public function testQueryBuilder(): void
    {
        $repository = $this->createMock(ProductVariantRepository::class);
        $repository->method('getClassName')->willReturn(TestProductVariant::class);

        $type = new ProductVariantAutocompleteField($repository);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $repository->expects(static::once())
            ->method('createQueryBuilderByEnabled')
            ->with('entity')
            ->willReturn($queryBuilder)
        ;

        $factory = $options['query_builder'];
        static::assertInstanceOf(\Closure::class, $factory);
        static::assertSame($queryBuilder, $factory($repository));
    }

    private function createOptionsResolver(): OptionsResolver
    {
        $repository = $this->createMock(ProductVariantRepository::class);
        $repository->method('getClassName')->willReturn(TestProductVariant::class);

        $type = new ProductVariantAutocompleteField($repository);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        return $resolver;
    }
}

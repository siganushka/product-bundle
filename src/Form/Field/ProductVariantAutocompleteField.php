<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Field;

use Doctrine\ORM\QueryBuilder;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProductVariantAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => ProductVariant::class,
            'placeholder' => 'product.name',
            'choice_label' => ChoiceList::label($this, [__CLASS__, 'createChoiceLabel']),
            // 'choice_attr' => fn (ProductVariant $variant): array => ['disabled' => $variant->isOutOfStock()],
            'max_results' => 20,
            'tom_select_options' => ['maxOptions' => 100],
            'searchable_fields' => ['product.name', 'choice1.text', 'choice2.text', 'choice3.text'],
            // 'filter_query' => function (QueryBuilder $queryBuilder, string $query): void {
            //     if (!$query) {
            //         return;
            //     }

            //     $queryBuilder->join('entity.product', 'p')
            //         ->andWhere('p.name LIKE :query')
            //         ->setParameter('query', '%'.$query.'%')
            //     ;
            // },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

    public static function createChoiceLabel(ProductVariant $variant): ?string
    {
        $product = $variant->getProduct();
        $label = $variant->getChoiceLabel();

        if (null === $product) {
            return $label;
        }

        $productName = $product->getName();
        if (\is_string($productName) && \is_string($label)) {
            return sprintf('%s【%s】', $productName, $label);
        }

        return $productName;
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProductVariantAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => ProductVariant::class,
            'placeholder' => 'product.name',
            'choice_label' => ChoiceList::label($this, [__CLASS__, 'createChoiceLabel']),
            'max_results' => 20,
            'tom_select_options' => ['maxOptions' => 100],
            'searchable_fields' => ['product.name', 'choice1.text', 'choice2.text', 'choice3.text'],
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
            return \sprintf('%s【%s】', $productName, $label);
        }

        return $productName;
    }
}

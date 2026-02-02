<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Field;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProductVariantAutocompleteField extends AbstractType
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choiceLabel = static function (ProductVariant $variant): ?string {
            $productName = $variant->getProduct()?->getName();
            if ($variantName = $variant->getName()) {
                return \sprintf('%s【%s】', $productName, $variantName);
            }

            return $productName;
        };

        $resolver->setDefaults([
            'class' => $this->repository->getClassName(),
            'choice_label' => ChoiceList::label($this, $choiceLabel),
            'query_builder' => static fn (ProductVariantRepository $er) => $er->createQueryBuilderByEnabled('entity'),
            'max_results' => 20,
            'tom_select_options' => ['maxOptions' => 100],
            'searchable_fields' => ['product.name', 'name'],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}

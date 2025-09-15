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
        $resolver->setDefaults([
            'class' => $this->repository->getClassName(),
            'choice_label' => ChoiceList::label($this, fn (ProductVariant $choice) => $choice->getName()),
            'query_builder' => fn (ProductVariantRepository $er) => $er->createQueryBuilderWithEnabled('entity'),
            'max_results' => 20,
            'tom_select_options' => ['maxOptions' => 100],
            'searchable_fields' => ['product.name', 'choice1.text', 'choice2.text', 'choice3.text'],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}

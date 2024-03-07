<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class ProductOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'option.name',
                'constraints' => new NotBlank(),
            ])
            ->add('values', CollectionType::class, [
                'label' => 'option.values',
                'entry_type' => ProductOptionValueType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'by_reference' => false,
                // [important] Using nested collections
                'prototype_name' => '__PRODUCT_OPTION_VALUES__',
                'constraints' => [
                    new Count(['min' => 2, 'minMessage' => 'option.values.min_count.invalid']),
                    new Unique([
                        'message' => 'product.variant.option_values.unique',
                        'normalizer' => fn (ProductOptionValue $value) => $value->getCode() ?? spl_object_hash($value),
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
        ]);
    }
}

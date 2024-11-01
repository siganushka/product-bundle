<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Symfony\Component\Form\AbstractType;
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
        $type = $options['values_as_text']
            ? ProductOptionValuesTextType::class
            : ProductOptionValuesCollectionType::class;

        $builder
            ->add('name', TextType::class, [
                'label' => 'product_option.name',
                'constraints' => new NotBlank(),
            ])
            ->add('values', $type, [
                'label' => 'product_option.values',
                'attr' => ['data-collection-class' => 'col-8'],
                'constraints' => [
                    new Count(min: 1),
                    new Unique(normalizer: fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value)),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
            'values_as_text' => false,
        ]);
    }
}

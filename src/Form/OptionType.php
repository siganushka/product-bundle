<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\Contracts\Doctrine\SortableInterface;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\OptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class OptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $prototypeData = new OptionValue();
        $prototypeData->setSort(SortableInterface::DEFAULT_SORT);

        $builder
            ->add('name', TextType::class, [
                'label' => 'option.name',
                'constraints' => new NotBlank(),
            ])
            ->add('values', CollectionType::class, [
                'label' => 'option.values',
                'entry_type' => OptionValueType::class,
                'entry_options' => ['label' => false],
                'prototype_data' => $prototypeData,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'by_reference' => false,
                'constraints' => new Count(['min' => 2, 'minMessage' => 'option.values.count.invalid']),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Option::class,
        ]);
    }
}

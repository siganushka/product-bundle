<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\Type\ProductOptionValueInputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'label' => 'product_option.name',
                'constraints' => new NotBlank(),
                'priority' => 2,
            ])
        ;

        $valuesOptions = [
            'label' => 'product_option.values',
            'priority' => 1,
            'constraints' => [
                new Count(['min' => 2, 'minMessage' => 'product_option.values.min_count']),
                new Unique([
                    'message' => 'product_option.values.unique',
                    'normalizer' => fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value),
                ]),
            ],
        ];

        if ($options['using_tagsinput']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, fn (FormEvent $event) => $this->onPreSetData($event, $valuesOptions));
        } else {
            $this->addValueCollectionField($builder, $valuesOptions)
                ->add('sort', IntegerType::class, ['label' => 'generic.sort'])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
            'using_tagsinput' => false,
        ]);
    }

    public function onPreSetData(FormEvent $event, array $defaultOptions = []): void
    {
        $data = $event->getData();
        $values = $data instanceof ProductOption ? $data->getValues() : new ArrayCollection();

        $event->getForm()->add('values', ProductOptionValueInputType::class, array_replace($defaultOptions, [
            'values' => $values,
        ]));
    }

    private function addValueCollectionField(FormBuilderInterface $builder, array $defaultOptions = []): FormBuilderInterface
    {
        return $builder->add('values', CollectionType::class, array_replace($defaultOptions, [
            'entry_type' => ProductOptionValueType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
            // [important] Using nested collections
            'prototype_name' => '__PRODUCT_OPTION_VALUES__',
        ]));
    }
}

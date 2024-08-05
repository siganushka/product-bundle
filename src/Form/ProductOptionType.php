<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Symfony\Component\Form\AbstractType;
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
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $previousValues = $data instanceof ProductOption ? $data->getValues() : new ArrayCollection();

        $form = $event->getForm();
        $valuesAsTags = $form->getConfig()->getOption('values_as_tags');

        $type = $valuesAsTags
            ? ProductOptionValuesTextType::class
            : ProductOptionValuesCollectionType::class;

        $form->add('values', $type, [
            'label' => 'product_option.values',
            'constraints' => [
                new Count(['min' => 1, 'minMessage' => 'product_option.values.min_count']),
                new Unique([
                    'message' => 'product_option.values.unique',
                    'normalizer' => fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value),
                ]),
            ],
            ...$valuesAsTags ? ['previous_values' => $previousValues] : [],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
            'values_as_tags' => false,
        ]);
    }
}

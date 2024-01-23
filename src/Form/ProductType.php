<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Media\ProductImg;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => new NotBlank(),
            ])
            ->add('img', MediaType::class, [
                'label' => 'product.img',
                'channel' => ProductImg::class,
                'constraints' => new NotBlank(),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof Product) {
            return;
        }

        $form = $event->getForm();
        $form->add('options', EntityType::class, [
            'label' => 'product.options',
            'class' => Option::class,
            'choice_label' => fn (Option $choice): string => (string) $choice,
            'disabled' => $data->getId() ? true : false,
            'multiple' => true,
            'expanded' => true,
        ]);

        if (null === $data->getId()) {
            return;
        }

        $prototypeData = new ProductVariant();
        $prototypeData->setProduct($data);

        $form->add('variants', CollectionType::class, [
            'label' => 'product.variants',
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false],
            'prototype_data' => $prototypeData,
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\ProductVariantCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function preSetData(FormEvent $event): void
    {
        $product = $event->getData();
        if (!$product instanceof Product) {
            return;
        }

        $prototypeData = new ProductVariant();
        $prototypeData->setProduct($product);

        $form = $event->getForm();
        $form->add('variants', ProductVariantCollectionType::class, [
            'label' => 'product.variants',
            'prototype_data' => $prototypeData,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            if (!$data instanceof Product) {
                return;
            }

            $method = $data->isOptionally() ? 'addVariantsField' : 'addDefaultVariantField';
            \call_user_func([$this, $method], $event->getForm(), $data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function addDefaultVariantField(FormInterface $form): void
    {
        $form->add('defaultVariant', ProductVariantType::class, [
            'label' => 'product.variants',
        ]);
    }

    public function addVariantsField(FormInterface $form, Product $product): void
    {
        $prototypeData = new ProductVariant();
        $prototypeData->setProduct($product);

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

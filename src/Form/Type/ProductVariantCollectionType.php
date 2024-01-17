<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantCollectionType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['product'] = $options['product'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('entry_options', function (Options $options, array $entryOptions) {
            $entryOptions['block_prefix'] = sprintf('%s_entry', $options['block_prefix']);

            return $entryOptions;
        });

        $prototypeData = function (Options $options): ProductVariant {
            $prototypeData = new ProductVariant();
            $prototypeData->setProduct($options['product']);

            return $prototypeData;
        };

        $resolver->setDefaults([
            'block_prefix' => 'siganushka_product_variant_collection',
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false],
            'prototype_data' => $prototypeData,
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
            'product' => null,
        ]);

        $resolver->setAllowedTypes('prototype_data', ['null', ProductVariant::class]);
        $resolver->setAllowedTypes('product', ['null', Product::class]);
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}

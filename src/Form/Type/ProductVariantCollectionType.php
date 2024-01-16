<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('entry_options', function (Options $options, $entryOptions) {
            $entryOptions['block_prefix'] = sprintf('%s_entry', $options['block_prefix']);
            // [important] The empty_data option must be \Closure and clone to generate new data
            $entryOptions['empty_data'] = fn (FormInterface $form) => $options['prototype_data'] ? clone $options['prototype_data'] : null;

            return $entryOptions;
        });

        $resolver->setDefaults([
            'block_prefix' => 'siganushka_product_variant_collection',
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
        ]);

        $resolver->setAllowedTypes('prototype_data', ['null', ProductVariant::class]);
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}

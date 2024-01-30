<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('choices', fn (Options $options): iterable => $options['product']->getVariants());

        $resolver->setDefaults([
            'choice_label' => 'optionValues.label',
            'choice_value' => 'id',
            'choice_attr' => fn (ProductVariant $variant): array => ['disabled' => $variant->isOutOfStock()],
            'choice_translation_domain' => false,
            'expanded' => false,
        ]);

        $resolver->setRequired('product');
        $resolver->setAllowedTypes('product', Product::class);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

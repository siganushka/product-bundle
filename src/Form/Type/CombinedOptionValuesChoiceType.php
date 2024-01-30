<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CombinedOptionValuesChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('choices', fn (Options $options): iterable => $options['product']->getCombinedOptionValues());

        $resolver->setDefaults([
            'choice_value' => 'value',
            'choice_label' => 'label',
            'choice_translation_domain' => false,
        ]);

        $resolver->setRequired('product');
        $resolver->setAllowedTypes('product', Product::class);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

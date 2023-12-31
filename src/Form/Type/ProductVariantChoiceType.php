<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('choices', fn (Options $options): array => $options['product']->getVariantChoices());

        $resolver->setDefaults([
            'choice_translation_domain' => false,
            'choice_value' => 'value',
            'choice_label' => fn (OptionValueCollection $choice): string => (string) $choice,
        ]);

        $resolver->setRequired('product');
        $resolver->setAllowedTypes('product', Product::class);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

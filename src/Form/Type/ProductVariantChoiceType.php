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
        $resolver->setNormalizer('choices', fn (Options $options) => $options['product']->getOptionValueChoices());

        $resolver->setDefaults([
            'choice_translation_domain' => false,
            'choice_value' => fn (?OptionValueCollection $choice) => $choice ? $choice->getValue() : '',
            'choice_label' => static fn (Options $options): ?\Closure => fn (OptionValueCollection $choice): string => sprintf('%s【%s】', (string) $options['product']->getName(), $choice->getLabel()),
        ]);

        $resolver->setRequired('product');
        $resolver->setAllowedTypes('product', Product::class);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

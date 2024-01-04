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
        $resolver->setNormalizer('choices', function (Options $options): array {
            /** @var Product|null */
            $product = $options['product'];

            return $product ? $product->getVariantChoices() : [];
        });

        $resolver->setDefaults([
            'product' => null,
            'choice_translation_domain' => false,
            'choice_value' => 'value',
            'choice_label' => fn (OptionValueCollection $choice): string => (string) $choice,
        ]);

        $resolver->setAllowedTypes('product', ['null', Product::class]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\OptionValue;
use Siganushka\ProductBundle\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('choices', function (Options $options) {
            if ($options['product'] instanceof Product) {
                return $options['product']->getOptionsMapping();
            }

            return null;
        });

        $resolver->setDefaults([
            'choice_label' => [__CLASS__, 'createChoiceLabel'],
            'choice_value' => [__CLASS__, 'createChoiceValue'],
            // 'choice_attr' => [__CLASS__, 'createChoiceAttr'],
            'choice_translation_domain' => false,
            'empty_data' => new ArrayCollection(),
            'product' => null,
        ]);

        $resolver->setAllowedTypes('product', ['null', Product::class]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public static function createChoiceLabel(Collection $choice): string
    {
        return implode('/', $choice->map(fn (OptionValue $optionValue) => $optionValue->getText())->toArray());
    }

    public static function createChoiceValue(?Collection $choice): ?string
    {
        if (null === $choice) {
            return null;
        }

        return implode('_', $choice->map(fn (OptionValue $optionValue) => $optionValue->getCode())->toArray());
    }

    public static function createChoiceAttr(Collection $choice): array
    {
        dd($choice);
    }
}

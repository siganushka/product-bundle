<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\String\u;

class ProductOptionValuesTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn (?Collection $data) => $this->productOptionValuesToString($data, $options['delimiter']),
            fn (?string $data) => $this->stringToProductOptionValues($data, $options['delimiter'], $options['previous_values']),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'previous_values' => new ArrayCollection(),
            'delimiter' => ',',
            'autocomplete' => true,
            'tom_select_options' => function (Options $options) {
                return [
                    'create' => true,
                    'createOnBlur' => true,
                    'duplicates' => true,
                    'delimiter' => $options['delimiter'],
                ];
            },
        ]);

        $resolver->setAllowedTypes('previous_values', Collection::class);
        $resolver->setAllowedTypes('delimiter', 'string');

        // Trim delimiter options
        $resolver->setNormalizer('delimiter', fn (Options $options, string $delimiter) => trim($delimiter));
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    /**
     * @param Collection<int, ProductOptionValue>|null $value
     */
    private function productOptionValuesToString(?Collection $value, string $delimiter): ?string
    {
        if (null === $value) {
            return null;
        }

        return implode($delimiter, $value->map(fn (ProductOptionValue $item) => $item->getText())->toArray());
    }

    /**
     * @param non-empty-string                    $delimiter
     * @param Collection<int, ProductOptionValue> $previousValues
     */
    private function stringToProductOptionValues(?string $value, string $delimiter, Collection $previousValues): array
    {
        if (null === $value || u($value)->isEmpty()) {
            return [];
        }

        $newTexts = explode($delimiter, $value);
        $newTexts = array_map('trim', $newTexts);
        $newTexts = array_filter($newTexts);

        $values = [];
        foreach ($newTexts as $text) {
            $filtered = $previousValues->filter(fn (ProductOptionValue $value) => $value->getText() === $text);
            $values[] = $filtered->first() ?: new ProductOptionValue(null, $text);
        }

        return $values;
    }
}

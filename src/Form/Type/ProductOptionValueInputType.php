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

class ProductOptionValueInputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Collection<int, ProductOptionValue> */
        $originValues = $options['values'];
        /** @var string */
        $delimiter = $options['delimiter'];

        $builder->addModelTransformer(new CallbackTransformer(
            fn (?Collection $data) => $this->productOptionValuesToString($data, $delimiter),
            fn (?string $data) => $this->stringToProductOptionValues($data, $delimiter, $originValues),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('values', new ArrayCollection());
        $resolver->setDefault('delimiter', ',');
        $resolver->setDefault('attr', ['data-toggle' => 'tagsinput']);

        $resolver->setAllowedTypes('values', Collection::class);
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

        $texts = $value->map(fn (ProductOptionValue $item) => $item->getText());

        return implode($delimiter, $texts->toArray());
    }

    /**
     * @param Collection<int, ProductOptionValue> $originValues
     *
     * @return Collection<int, ProductOptionValue>
     */
    private function stringToProductOptionValues(?string $value, string $delimiter, Collection $originValues): array
    {
        if (null === $value || u($value)->isEmpty()) {
            return [];
        }

        $texts = explode($delimiter, $value);
        $texts = array_map('trim', $texts);
        $texts = array_unique($texts);
        $texts = array_filter($texts);

        $values = $valuesText = [];
        foreach ($originValues as $value) {
            if (\in_array($value->getText(), $texts)) {
                $values[] = $value;
                $valuesText[] = $value->getText();
            }
        }

        foreach (array_diff($texts, $valuesText) as $text) {
            $values[] = new ProductOptionValue($text);
        }

        return $values;
    }
}

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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\String\u;

class ProductOptionValuesTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn (?Collection $values) => $this->valuesToString($values, $options['delimiter']),
            // Reverse transform by event.
            fn (array $newValues) => $newValues,
        ));

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options): void {
            /** @var Collection<int, ProductOptionValue> */
            $previousValues = $event->getForm()->getData() ?? new ArrayCollection();

            $newValues = $this->stringToValues($event->getData(), $options['delimiter']);
            foreach ($newValues as $key => $value) {
                $filtered = $previousValues->filter(fn (ProductOptionValue $item) => $item->getText() === $value->getText());
                $newValues[$key] = $filtered->first() ?: $value;
            }

            $event->setData($newValues);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'delimiter' => ',',
            'autocomplete' => true,
            'tom_select_options' => fn (Options $options) => [
                'create' => true,
                'createOnBlur' => true,
                'duplicates' => true,
                'delimiter' => $options['delimiter'],
            ],
        ]);

        $resolver->setAllowedTypes('delimiter', 'string');
        $resolver->setNormalizer('delimiter', fn (Options $options, string $delimiter) => trim($delimiter));
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    private function valuesToString(?Collection $values, string $delimiter): ?string
    {
        if (null === $values) {
            return null;
        }

        return implode($delimiter, $values->map(fn (ProductOptionValue $item) => $item->getText())->toArray());
    }

    /**
     * @param non-empty-string $delimiter
     *
     * @return array<int, ProductOptionValue>
     */
    private function stringToValues(?string $valuesAsString, string $delimiter): array
    {
        if (null === $valuesAsString || u($valuesAsString)->isEmpty()) {
            return [];
        }

        $newTexts = explode($delimiter, $valuesAsString);
        $newTexts = array_map('trim', $newTexts);
        $newTexts = array_filter($newTexts);

        return array_map(fn (string $text) => new ProductOptionValue(null, $text), $newTexts);
    }
}

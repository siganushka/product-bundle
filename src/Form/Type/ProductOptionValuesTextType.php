<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValuesTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ProductOptionValuesToStringTransformer($options['delimiter']));

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var Collection<int, ProductOptionValue> */
            $previousValues = $event->getForm()->getData() ?? new ArrayCollection();
            /** @var array<int, ProductOptionValue> */
            $newValues = $event->getData();
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
}

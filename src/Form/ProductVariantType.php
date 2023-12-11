<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\OptionValue;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product.variant.name',
                'constraints' => new NotBlank(),
            ])
            ->add('price', NumberType::class, [
                'label' => 'product.variant.price',
                'constraints' => new NotBlank(),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        /** @var ProductVariant */
        $variant = $event->getData();
        if (null === $variant || null === $product = $variant->getProduct()) {
            return;
        }

        $options = $product->getOptions();
        if ($options->isEmpty()) {
            $this->createNoneOptions($event);
        } else {
            $this->createMultipleOptions($event, $product);
        }
    }

    public function createNoneOptions(FormEvent $event): void
    {
        $form = $event->getForm();
        $form->add('price', NumberType::class);
    }

    public function createMultipleOptions(FormEvent $event, Product $product): void
    {
        $choices = $product->getVariantsChoices();

        $form = $event->getForm();
        $form->add('optionValues', ChoiceType::class, [
            'label' => 'product.variant.option_values',
            'placeholder' => '_choice_empty',
            'choices' => array_keys($choices),
            'choice_label' => function (?string $key) use ($choices) {
                $optionValueTexts = array_map(fn (OptionValue $optionValue) => $optionValue->getText(), $choices[$key]);

                return implode('/', $optionValueTexts);
            },
            'choice_translation_domain' => false,
        ]);
    }
}

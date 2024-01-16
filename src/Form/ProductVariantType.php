<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', CentsMoneyType::class, [
                'label' => 'product.variant.price',
                'constraints' => new NotBlank(),
            ])
            ->add('inventory', IntegerType::class, [
                'label' => 'product.variant.inventory',
                'constraints' => [
                    new GreaterThanOrEqual(0),
                    new LessThanOrEqual(2147483647),
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'generic.enabled',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();

            $emptyData = $form->getConfig()->getEmptyData();
            if ($emptyData instanceof \Closure) {
                $emptyData = $emptyData($form);
            }

            $variant = $data ?? $emptyData;
            if ($variant instanceof ProductVariant) {
                $this->formModifier($form, $variant);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
            'constraints' => [
                new Callback(function (ProductVariant $variant, ExecutionContextInterface $context): void {
                    $product = $variant->getProduct();
                    if (!$product) {
                        return;
                    }

                    $filtered = $product->getVariants();

                    if (!$product->getOptions()->isEmpty()) {
                        $filtered = $filtered->filter(fn (ProductVariant $item) => $item->getChoice()->getValue());
                    }

                    $filtered = $filtered->filter(fn (ProductVariant $item) => $item->getChoice()->equals($variant->getChoice()));

                    if ($filtered->count() > 1) {
                        $context->buildViolation('product.variant.choice.used')
                            ->atPath('choice')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }

    public function formModifier(FormInterface $form, ProductVariant $variant): void
    {
        $product = $variant->getProduct();
        if (!$product) {
            return;
        }

        $choices = $product->getVariantChoices();
        if (0 === \count($choices)) {
            return;
        }

        $variants = $product->getVariants();
        $usedChoices = $variants->map(fn (ProductVariant $item) => $item->getChoice()->getValue());

        $form->add('choice', ChoiceType::class, [
            'label' => 'product.variant.choice',
            'choices' => $choices,
            'choice_translation_domain' => false,
            'choice_value' => 'value',
            'choice_label' => 'label',
            'choice_attr' => function (ProductVariantChoice $choice) use ($usedChoices, $variant): array {
                if ($choice->equals($variant->getChoice())) {
                    return ['disabled' => false];
                }

                return ['disabled' => $usedChoices->contains($choice->getValue())];
            },
            'disabled' => $variant && $variant->getId() ? true : false,
            'placeholder' => 'generic.choice',
            'constraints' => new NotBlank(),
            'priority' => 1,
        ]);
    }
}

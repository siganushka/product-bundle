<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
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
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();

            // Using prototype_data for embed collection
            $parent = $form->getParent();
            $prototypeData = $parent ? $parent->getConfig()->getOption('prototype_data') : null;

            $variant = $data ?? $prototypeData;
            if ($variant instanceof ProductVariant) {
                $this->formModifier($form, $variant);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
            'constraints' => new UniqueEntity([
                'fields' => ['product', 'choiceValue'],
                'errorPath' => 'choice',
                'message' => 'product.variant.choice.unique',
                'ignoreNull' => false,
            ]),
        ]);
    }

    public function formModifier(FormInterface $form, ProductVariant $variant): void
    {
        $product = $variant->getProduct();
        if (null === $product || !$product->isOptionally()) {
            return;
        }

        $variants = $product->getVariants();
        $usedChoices = $variants->map(fn (ProductVariant $item) => $item->getChoiceValue());

        $form->add('choice', ChoiceType::class, [
            'label' => 'product.variant.choice',
            'choices' => $product->getVariantChoices(),
            'choice_translation_domain' => false,
            'choice_value' => 'value',
            'choice_label' => 'label',
            'choice_attr' => function (ProductVariantChoice $choice) use ($usedChoices, $variant): array {
                if ($choice->equals($variant->getChoice())) {
                    return ['disabled' => false];
                }

                return ['disabled' => $usedChoices->contains($choice->getValue())];
            },
            'disabled' => null !== $variant->getId(),
            'placeholder' => 'generic.choice',
            'constraints' => [
                new NotBlank(),
                // Validate unique for embed collection
                new Callback(function (?ProductVariantChoice $choice, ExecutionContextInterface $context) use ($variants): void {
                    if (null === $choice) {
                        return;
                    }

                    $newVariants = $variants->filter(fn (ProductVariant $item) => null === $item->getId() && $choice->equals($item->getChoice()));
                    if ($newVariants->count() > 1) {
                        $context->buildViolation('product.variant.choice.repeat')
                            ->atPath('choice')
                            ->addViolation();
                    }
                }),
            ],
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?ProductVariantChoice $choice): void {
                $choice && $variant->setChoice($choice);
            },
        ]);
    }
}

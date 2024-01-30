<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Form\Type\CombinedOptionValuesChoiceType;
use Siganushka\ProductBundle\Model\CombinedOptionValues;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
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
                'fields' => ['product', 'code'],
                'errorPath' => 'optionValues',
                'message' => 'product.variant.option_values.unique',
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
        $usedChoices = $variants->map(fn (ProductVariant $item) => $item->getCode());

        $form->add('optionValues', CombinedOptionValuesChoiceType::class, [
            'label' => 'product.variant.option_values',
            'choice_attr' => function (CombinedOptionValues $optionValues) use ($usedChoices, $variant): array {
                if ($optionValues->equalsTo($variant->getOptionValues())) {
                    return ['disabled' => false];
                }

                return ['disabled' => $usedChoices->contains($optionValues->getValue())];
            },
            'disabled' => null !== $variant->getId(),
            'product' => $product,
            'placeholder' => 'generic.choice',
            'constraints' => [
                new NotBlank(),
                // Validate unique for embed collection
                new Callback(function (?CombinedOptionValues $optionValues, ExecutionContextInterface $context) use ($variants): void {
                    if (null === $optionValues) {
                        return;
                    }

                    $newVariants = $variants->filter(fn (ProductVariant $item) => null === $item->getId() && $optionValues->equalsTo($item->getOptionValues()));
                    if ($newVariants->count() > 1) {
                        $context->buildViolation('product.variant.option_values.repeat')
                            ->atPath('optionValues')
                            ->addViolation();
                    }
                }),
            ],
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?CombinedOptionValues $optionValues): void {
                $optionValues && $variant->setOptionValues($optionValues);
            },
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Form\Type\ProductVariantChoiceType;
use Siganushka\ProductBundle\Model\VariantChoice;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
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
            'using_collection' => false,
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $variant = $event->getData();
        if (!$variant instanceof ProductVariant) {
            return;
        }

        $product = $variant->getProduct();
        if (!$product instanceof Product) {
            return;
        }

        $options = $product->getOptions();
        if ($options->isEmpty()) {
            return;
        }

        $form = $event->getForm();
        $form->add('choice', ProductVariantChoiceType::class, [
            'label' => 'product.variant.choice',
            'choice_attr' => function (VariantChoice $choice) use ($product, $variant): array {
                if ($choice->equals($variant->getChoice())) {
                    return ['disabled' => false];
                }

                $variants = $product->getVariants();
                $choices = $variants->map(fn (ProductVariant $item) => $item->getChoice()->getValue());

                return ['disabled' => $choices->contains($choice->getValue())];
            },
            'placeholder' => 'generic.choice',
            'constraints' => new NotBlank(),
            'disabled' => $variant->getId() ? true : false,
            'product' => $product,
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?VariantChoice $choice): void {
                $choice && $variant->setChoice($choice);
            },
        ]);
    }
}

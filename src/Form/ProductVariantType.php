<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\EventListener\AddInventoryFieldListener;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Model\VariantChoice;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('price', CentsMoneyType::class, [
                'label' => 'product.variant.price',
                'constraints' => new NotBlank(),
            ])
            ->add('inventoryTracking', CheckboxType::class, [
                'label' => 'product.variant.inventory_tracking',
            ])
        ;

        $builder->get('inventoryTracking')->addEventSubscriber(new AddInventoryFieldListener());

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
            'allow_extra_fields' => true,
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

        $choices = $product->getVariantChoices();
        if (0 === \count($choices)) {
            return;
        }

        $form = $event->getForm();
        $form->add('choice', ChoiceType::class, [
            'label' => 'product.variant.choice',
            'choices' => $choices,
            'choice_translation_domain' => false,
            'choice_value' => 'value',
            'choice_label' => 'label',
            'choice_attr' => function (VariantChoice $choice) use ($product, $variant): array {
                if ($choice->equals($variant->getChoice())) {
                    return ['disabled' => false];
                }

                $v = new ProductVariant();
                $v->setChoice($choice);

                return ['disabled' => $product->hasVariant($v)];
            },
            'disabled' => $variant->getId() ? true : false,
            'placeholder' => 'generic.choice',
            'constraints' => new NotBlank(),
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?VariantChoice $choice): void {
                $choice && $variant->setChoice($choice);
            },
        ]);
    }
}

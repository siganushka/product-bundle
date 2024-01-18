<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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

        if ($options['using_collection']) {
            $builder->add('checked', CheckboxType::class, [
                'label' => false,
                'priority' => 10,
            ]);
        }

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
            'using_collection' => false,
            'validation_groups' => function (FormInterface $form) {
                $usingCollection = $form->getConfig()->getOption('using_collection');
                if (!$usingCollection) {
                    return 'Default';
                }

                return $form['checked']->getData() ? 'Default' : null;
            },
        ]);
    }

    public function formModifier(FormInterface $form, ProductVariant $variant): void
    {
        $product = $variant->getProduct();
        if (null === $product || false === $product->isOptionally()) {
            return;
        }

        $variants = $product->generateVariantChoices();
        $choices = array_map(fn (ProductVariant $variant) => $variant->getChoice(), $variants);

        // Disable on using collection or edit
        $usingCollection = $form->getConfig()->getOption('using_collection');
        $disabled = ($usingCollection || $variant->getId()) ? true : false;

        $form->add('choice', ChoiceType::class, [
            'label' => 'product.variant.choice',
            'choices' => $choices,
            'choice_translation_domain' => false,
            'choice_value' => 'value',
            'choice_label' => 'label',
            'disabled' => $disabled,
            'placeholder' => 'generic.choice',
            'constraints' => new NotBlank(),
            'priority' => 1,
        ]);
    }
}

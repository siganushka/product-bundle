<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Media\ProductImg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('img', MediaType::class, [
                'label' => 'product_variant.img',
                'channel' => ProductImg::class,
                'priority' => 2,
                // Setting label from CollectionType
                'style' => false === $options['label'] ? 'width: 38px; height: 38px' : null,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'product_variant.price',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('inventory', IntegerType::class, [
                'label' => 'product_variant.inventory',
                'constraints' => new GreaterThanOrEqual(0),
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
        $data = $event->getData();
        if (!$data instanceof ProductVariant) {
            return;
        }

        $choiceLabel = $data->getChoiceLabel();
        if (null === $choiceLabel) {
            return;
        }

        $event->getForm()->add('choiceLabel', TextType::class, [
            'label' => 'product_variant.choice',
            'disabled' => true,
            'priority' => 1,
        ]);
    }
}

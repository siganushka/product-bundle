<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Media\ProductVariantImg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                'channel' => ProductVariantImg::class,
                'priority' => 2,
                // Setting label from CollectionType
                'style' => false === $options['label'] ? 'width: 38px; height: 38px' : null,
            ])
            ->add('price', CentsMoneyType::class, [
                'label' => 'product_variant.price',
                'constraints' => new NotBlank(),
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

        $choice = $data->getChoice();
        if ($choice->isEmpty()) {
            return;
        }

        $event->getForm()->add('choice', TextType::class, [
            'label' => 'product_variant.option_values',
            'property_path' => 'choice.label',
            'disabled' => true,
            'priority' => 1,
        ]);
    }
}

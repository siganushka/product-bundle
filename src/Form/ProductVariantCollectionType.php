<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('variants', CollectionType::class, [
                'label' => 'product.variants',
                'entry_type' => ProductVariantType::class,
                'entry_options' => ['label' => false],
                'error_bubbling' => false,
                'by_reference' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof Product) {
            return;
        }

        $choices = $data->isOptionally() ? $data->getChoices() : [new ProductVariantChoice()];
        foreach ($choices as $choice) {
            $data->addVariant(new ProductVariant($data, $choice));
        }
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Option;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Media\ProductImg;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => [
                    new NotBlank(),
                    new Length(null, null, 128),
                ],
            ])
            ->add('img', MediaType::class, [
                'label' => 'product.img',
                'channel' => ProductImg::class,
                'constraints' => new NotBlank(),
                'accept' => 'image/*',
                'width' => '320px',
                'height' => '320px',
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
        $disabled = $data instanceof Product && $data->getId() ? true : false;

        $form = $event->getForm();
        $form->add('options', EntityType::class, [
            'label' => 'product.options',
            'class' => Option::class,
            'choice_label' => fn (Option $choice): string => (string) $choice,
            'disabled' => $disabled,
            'multiple' => true,
            'expanded' => true,
        ]);
    }
}

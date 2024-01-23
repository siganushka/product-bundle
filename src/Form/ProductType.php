<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Form\EventListener\AddProductOptionsListener;
use Siganushka\ProductBundle\Form\EventListener\AddProductVariantsListener;
use Siganushka\ProductBundle\Media\ProductImg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => new NotBlank(),
            ])
            ->add('img', MediaType::class, [
                'label' => 'product.img',
                'channel' => ProductImg::class,
                'constraints' => new NotBlank(),
            ])
        ;

        $builder->addEventSubscriber(new AddProductOptionsListener());
        $builder->addEventSubscriber(new AddProductVariantsListener());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Media\ProductImg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

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
        $persisted = $data instanceof Product && null !== $data->getId();

        $form = $event->getForm();
        $form->add('options', CollectionType::class, [
            'label' => 'product.options',
            'entry_type' => ProductOptionSimpleType::class,
            'entry_options' => ['label' => false],
            'allow_add' => !$persisted,
            'allow_delete' => !$persisted,
            'error_bubbling' => false,
            'by_reference' => false,
            'constraints' => [
                new Count(['max' => 3, 'maxMessage' => 'product_option.max_count']),
                new Unique([
                    'message' => 'product_option.unique',
                    'normalizer' => fn (ProductOption $option) => $option->getName() ?? spl_object_hash($option),
                ]),
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductVariantType extends AbstractType
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', MoneyType::class, [
                'label' => 'product_variant.price',
                'row_attr' => false === $options['label'] ? ['class' => 'col-4'] : [],
                'constraints' => [
                    new NotBlank(groups: ['PriceRequired']),
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('inventory', IntegerType::class, [
                'label' => 'product_variant.inventory',
                'row_attr' => false === $options['label'] ? ['class' => 'col-2'] : [],
                'constraints' => new GreaterThanOrEqual(0),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->onPreSetData(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                return $data instanceof ProductVariant && $data->isEnabled()
                    ? ['Default', 'PriceRequired']
                    : ['Default'];
            },
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if ($data instanceof ProductVariant && $data->getValue()) {
            $form = $event->getForm();
            $label = $form->getConfig()->getOption('label');

            $form->add('label', TextType::class, [
                'label' => 'product_variant.choice',
                'disabled' => true,
                'priority' => 1,
            ])
                ->add('enabled', CheckboxType::class, [
                    'label' => false === $label ? false : 'generic.enable',
                    'row_attr' => false === $label ? ['class' => 'w-0 pt-2'] : [],
                    'priority' => false === $label ? 8 : -8,
                ])
            ;
        }
    }
}

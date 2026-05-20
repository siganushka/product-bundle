<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
                'constraints' => [
                    new NotBlank(groups: ['PriceRequired']),
                    new GreaterThanOrEqual(0),
                ],
                'row_attr' => false === $options['label'] ? ['class' => 'w-25'] : [],
                'required' => false,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'product_variant.stock',
                'constraints' => new GreaterThanOrEqual(0),
                'row_attr' => false === $options['label'] ? ['class' => 'w-25'] : [],
                'required' => false,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => false === $options['label'] ? false : 'generic.enable',
                'label_attr' => ['class' => 'checkbox-switch'],
                'row_attr' => false === $options['label'] ? ['class' => 'w-0 align-middle'] : [],
                'priority' => false === $options['label'] ? 100 : -100,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->onPreSetData(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'validation_groups' => static function (FormInterface $form) {
                $data = $form->getData();

                return $data instanceof ProductVariant && $data->isEnabled()
                    ? ['Default', 'PriceRequired']
                    : ['Default'];
            },
        ]);
    }

    public function onPreSetData(PreSetDataEvent $event): void
    {
        $data = $event->getData();
        if ($data instanceof ProductVariant && $data->getCode()) {
            $event->getForm()
                ->add('name', TextType::class, [
                    'label' => 'product_variant.name',
                    'priority' => 10,
                    'required' => false,
                    'disabled' => true,
                ])
            ;
        }
    }
}

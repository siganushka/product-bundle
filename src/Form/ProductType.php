<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class ProductType extends AbstractType
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('img', MediaType::class, [
                'label' => 'product.img',
                'channel' => 'product_img',
                'constraints' => new NotBlank(),
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => new NotBlank(),
            ])
            ->add('virtual', CheckboxType::class, [
                'label' => 'product.virtual',
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'product.weight',
                'constraints' => new NotBlank(groups: ['notVirtualRequired']),
            ])
            ->add('length', IntegerType::class, [
                'label' => 'product.length',
                'constraints' => new NotBlank(groups: ['notVirtualRequired']),
            ])
            ->add('width', IntegerType::class, [
                'label' => 'product.width',
                'constraints' => new NotBlank(groups: ['notVirtualRequired']),
            ])
            ->add('height', IntegerType::class, [
                'label' => 'product.height',
                'constraints' => new NotBlank(groups: ['notVirtualRequired']),
            ])
        ;

        $callable = $options['simple']
            ? $this->addVariantField(...)
            : $this->addOptionsField(...);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callable);
        $builder->addEventListener(FormEvents::SUBMIT, $this->clearVirtualField(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $simple = function (Options $options) {
            $data = $options['data'] ?? null;
            if ($data instanceof Product) {
                return $data->getOptions()->isEmpty();
            }

            return false;
        };

        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                return $data instanceof Product && $data->isVirtual()
                    ? ['Default']
                    : ['Default', 'notVirtualRequired'];
            },
            'simple' => $simple,
        ]);
    }

    public function addVariantField(FormEvent $event): void
    {
        $event->getForm()->add('variants', ProductVariantType::class, [
            'property_path' => 'variants[0]',
            // You need to manually set the association here
            'setter' => fn (Product &$product, ProductVariant $variant) => $product->addVariant($variant),
            'error_bubbling' => false,
        ]);
    }

    public function addOptionsField(FormEvent $event): void
    {
        $data = $event->getData();
        $persisted = $data instanceof Product && null !== $data->getId();

        $event->getForm()->add('options', CollectionType::class, [
            'label' => 'product.options',
            'entry_type' => ProductOptionType::class,
            'entry_options' => ['label' => false, 'simple' => true],
            'allow_add' => !$persisted,
            'allow_delete' => !$persisted,
            'error_bubbling' => false,
            'by_reference' => false,
            'constraints' => [
                new Count(min: 1, max: 3),
                new Unique(normalizer: fn (ProductOption $option) => $option->getName() ?? spl_object_hash($option)),
            ],
        ]);
    }

    public function clearVirtualField(FormEvent $event): void
    {
        $data = $event->getData();
        if ($data instanceof Product && $data->isVirtual()) {
            $data->setWeight(null);
            $data->setLength(null);
            $data->setWidth(null);
            $data->setHeight(null);
        }
    }
}

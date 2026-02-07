<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'rule' => 'product_img',
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $options['combinable']
            ? $this->addOptionsField(...)
            : $this->addVariantField(...)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $combinable = static function (Options $options) {
            $data = $options['data'] ?? null;
            if ($data instanceof Product) {
                return !$data->getOptions()->isEmpty();
            }

            return false;
        };

        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'validation_groups' => static fn (FormInterface $form) => $form->getConfig()->getOption('combinable')
                ? ['Default', 'OptionsRequired', 'ProductOption']
                : ['Default'],
            'combinable' => $combinable,
        ]);
    }

    public function addVariantField(PreSetDataEvent $event): void
    {
        $event->getForm()->add('variants', ProductVariantType::class, [
            'property_path' => 'variants[0]',
            'setter' => static fn (Product &$product, ProductVariant $variant) => $product->addVariant($variant),
            'error_bubbling' => false,
        ]);
    }

    public function addOptionsField(PreSetDataEvent $event): void
    {
        /** @var Product */
        $data = $event->getData();
        $persisted = null !== $data->getId();

        $event->getForm()->add('options', CollectionType::class, [
            'label' => 'product.options',
            'entry_type' => ProductOptionType::class,
            'entry_options' => ['label' => false, 'simple' => true],
            'allow_add' => !$persisted,
            'allow_delete' => !$persisted,
            'error_bubbling' => false,
            'by_reference' => false,
        ]);
    }
}

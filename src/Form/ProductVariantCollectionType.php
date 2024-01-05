<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductVariantCollectionType extends AbstractType
{
    private ProductVariantRepository $variantRepository;

    public function __construct(ProductVariantRepository $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'disabled' => true,
            ])
            ->add('variants', CollectionType::class, [
                'label' => 'product.variants',
                'entry_type' => ProductVariantType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'by_reference' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function preSetData(FormEvent $event): void
    {
        $product = $event->getData();
        if (!$product instanceof Product) {
            return;
        }

        $choices = $product->getVariantChoices();
        if (0 === \count($choices)) {
            $choices[] = new OptionValueCollection();
        }

        foreach ($choices as $optionValues) {
            if ($product->hasVariantChoice($optionValues)) {
                continue;
            }

            $variant = $this->variantRepository->createNew();
            $variant->setOptionValues($optionValues);
            $product->addVariant($variant);
        }

        $event->setData($product);
    }
}

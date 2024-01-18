<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
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
        $builder->add('variants', CollectionType::class, [
            'label' => 'product.variants',
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false, 'using_collection' => true],
            // 'allow_add' => false,
            // 'allow_delete' => false,
            // 'error_bubbling' => false,
            // 'by_reference' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $product = $event->getData();
        if (!$product instanceof Product) {
            return;
        }

        foreach ($product->generateVariantChoices() as $variant) {
            $filtered = $product->getVariants()->filter(fn (ProductVariant $item) => $item->getChoice()->equals($variant->getChoice()));

            if ($filtered->first()) {
                /** @var ProductVariant */
                $variant = $filtered->first();
                $variant->setChecked(true);
            }

            $product->addVariant($variant);
        }
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $product = $event->getData();
        if (!$product instanceof Product) {
            return;
        }

        foreach ($product->getVariants() as $variant) {
            if (!$variant->isChecked()) {
                $product->removeVariant($variant);
            }
        }
    }
}

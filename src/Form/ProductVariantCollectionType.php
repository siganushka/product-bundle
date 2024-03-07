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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Unique;

class ProductVariantCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            if (!$data instanceof Product) {
                return;
            }

            $method = $data->isOptionally() ? 'addVariantCollectionField' : 'addVariantField';
            \call_user_func([$this, $method], $event->getForm(), $data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function addVariantCollectionField(FormInterface $form, Product $product): void
    {
        $generatedVariants = $product->getGeneratedVariants();
        array_walk($generatedVariants, [$product, 'addVariant']);

        $form->add('variants', CollectionType::class, [
            'label' => 'product.variants',
            'entry_type' => ProductVariantType::class,
            'entry_options' => ['label' => false],
            'error_bubbling' => false,
            'by_reference' => false,
            'constraints' => new Unique([
                'message' => 'product.variant.option_values.unique',
                'normalizer' => fn (ProductVariant $variant) => $variant->getCode() ?? spl_object_hash($variant),
            ]),
        ]);
    }

    public function addVariantField(FormInterface $form, Product $product): void
    {
        $emptyData = new ProductVariant();
        $emptyData->setProduct($product);

        $form->add('variant', ProductVariantType::class, [
            'label' => 'product.variants',
            'property_path' => 'variants[0]',
            'empty_data' => $emptyData,
        ]);
    }
}

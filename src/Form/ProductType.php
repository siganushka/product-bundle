<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
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
            ->add('description', TextareaType::class, [
                'label' => 'product.description',
                'attr' => ['rows' => 3],
                'constraints' => new Length(max: 128),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $options['simple']
            ? $this->addVariantField(...)
            : $this->addOptionsField(...)
        );
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
}

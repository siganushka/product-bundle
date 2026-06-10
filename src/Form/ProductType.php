<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'constraints' => new NotBlank(),
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
            ])
            ->add('summary', TextType::class, [
                'label' => 'product.summary',
                'constraints' => new Length(max: 255),
                'required' => false,
            ])
        ;

        $callback = $options['combinable'] ? $this->addOptionsField(...) : $this->addVariantField(...);
        \call_user_func($callback, $builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'combinable' => false,
        ]);

        $resolver->setNormalizer('combinable', static function (Options $options, bool $combinable) {
            $data = $options['data'] ?? null;
            if ($data instanceof Product && $data->getId()) {
                return !$data->getOptions()->isEmpty();
            }

            return $combinable;
        });

        $resolver->setAllowedTypes('combinable', 'bool');
    }

    public function addVariantField(FormBuilderInterface $builder): void
    {
        $builder->add('variants', ProductVariantType::class, [
            'property_path' => 'variants[0]',
            'setter' => static fn (Product &$product, ProductVariant $variant) => $product->addVariant($variant),
            'error_bubbling' => false,
        ]);
    }

    public function addOptionsField(FormBuilderInterface $builder): void
    {
        $builder->add('options', CollectionType::class, [
            'label' => 'product.options',
            'entry_type' => ProductOptionType::class,
            'entry_options' => ['label' => false, 'simple' => true],
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
            'constraints' => new Count(min: 1),
        ]);
    }
}

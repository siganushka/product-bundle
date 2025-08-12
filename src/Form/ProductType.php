<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use BenTools\CartesianProduct\CartesianProduct;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class ProductType extends AbstractType
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly ProductVariantRepository $productVariantRepository)
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
                'constraints' => new Length(max: 100),
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $options['combinable']
            ? $this->addOptionsField(...)
            : $this->addVariantField(...)
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->generateVariants(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $combinable = function (Options $options) {
            $data = $options['data'] ?? null;
            if ($data instanceof Product) {
                return !$data->getOptions()->isEmpty();
            }

            return false;
        };

        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'combinable' => $combinable,
        ]);
    }

    public function addVariantField(PreSetDataEvent $event): void
    {
        $event->getForm()->add('variants', ProductVariantType::class, [
            'property_path' => 'variants[0]',
            // You need to manually set the association here
            'setter' => fn (Product &$product, ProductVariant $variant) => $product->addVariant($variant),
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
            'constraints' => [
                new Count(min: 1, max: 3),
                new Unique(normalizer: fn (ProductOption $option) => $option->getName() ?? spl_object_hash($option)),
            ],
        ]);
    }

    public function generateVariants(PostSubmitEvent $event): void
    {
        /** @var Product */
        $data = $event->getData();

        $variants = $data->getVariants();
        foreach ($this->generateVariantsChoice($data) as $index => $choice) {
            $variants[$index] = $variants->findFirst(fn ($_, ProductVariant $item) => $item->getChoiceValue() === $choice->value)
                ?? $this->productVariantRepository->createNew($choice)->setProduct($data)->setEnabled(false);
        }
    }

    /**
     * @return array<int, ProductVariantChoice>
     */
    private function generateVariantsChoice(Product $entity, bool $defaultChoiceOnEmptyOptions = false): array
    {
        $options = $entity->getOptions();
        if ($defaultChoiceOnEmptyOptions && $options->isEmpty()) {
            return [new ProductVariantChoice()];
        }

        $set = [];
        foreach ($options as $option) {
            $values = $option->getValues();
            if ($values->count()) {
                $set[] = $values;
            }
        }

        $cartesianProduct = new CartesianProduct($set);
        $asArray = $cartesianProduct->asArray();

        return array_map(fn (array $combinedOptionValues) => new ProductVariantChoice($combinedOptionValues), $asArray);
    }
}

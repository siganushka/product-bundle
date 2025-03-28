<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
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
                'channel' => 'product',
                'constraints' => new NotBlank(),
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => new NotBlank(),
            ])
        ;

        $callable = $options['simple']
            ? $this->addVariantField(...)
            : $this->addOptionsField(...);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $callable);
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
        $form = $event->getForm();
        $form->add('variant', ProductVariantType::class, [
            'property_path' => 'variants[0]',
            'error_bubbling' => false,
            'empty_data' => $this->productVariantRepository->createNew($event->getData()),
        ]);
    }

    public function addOptionsField(FormEvent $event): void
    {
        $data = $event->getData();
        $persisted = $data instanceof Product && null !== $data->getId();

        $form = $event->getForm();
        $form->add('options', CollectionType::class, [
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

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Form\Type\ProductVariantCollectionType;
use Siganushka\ProductBundle\Model\ProductVariantChoiceGenerator;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'rule' => 'product_img',
                'constraints' => new NotBlank(),
                'priority' => 0,
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
                'priority' => -10,
            ])
            ->add('summary', TextType::class, [
                'label' => 'product.summary',
                'constraints' => new Length(max: 255),
                'required' => false,
                'priority' => -20,
            ])
            ->add('options', CollectionType::class, [
                'label' => 'product.options',
                'entry_type' => ProductOptionType::class,
                'entry_options' => ['label' => false, 'simple' => true],
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'by_reference' => false,
                'required' => false,
                'priority' => -30,
            ])
            ->add('variants', ProductVariantCollectionType::class, [
                'label' => 'product.variants',
                'priority' => -35,
            ])
            ->add('minLimit', IntegerType::class, [
                'label' => 'product.min_limit',
                'constraints' => new GreaterThanOrEqual(1),
                'required' => false,
                'priority' => -40,
            ])
            ->add('maxLimit', IntegerType::class, [
                'label' => 'product.max_limit',
                'constraints' => new GreaterThanOrEqual(
                    propertyPath: 'parent.all[minLimit].data',
                    message: 'This value should be greater than or equal to min limit.',
                ),
                'required' => false,
                'priority' => -50,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            if ($data instanceof AbstractProduct) {
                foreach ($data->generateChoices() as $choice) {
                    $data->addVariant($this->productVariantRepository->createNew($choice));
                }
            }
        });

        $builder->get('options')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm()->getParent();
            $data = $event->getData();
            if ($form && $data instanceof Collection) {
                $this->formModifier($form, $data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
        ]);
    }

    /**
     * @param Collection<int, AbstractProductOption> $options
     */
    private function formModifier(FormInterface $form, Collection $options): void
    {
        $product = $form->getData();
        if (!$product instanceof AbstractProduct) {
            return;
        }

        $variants = [];
        foreach (ProductVariantChoiceGenerator::generate($options) as $choice) {
            $variants[] = $product->getVariants()->findFirst(static fn ($_, AbstractProductVariant $item) => $item->getCode() === $choice->code)
                ?? $this->productVariantRepository->createNew($choice);
        }

        $form->add('variants', ProductVariantCollectionType::class, [
            'label' => 'product.variants',
            'data' => new ArrayCollection($variants),
            'priority' => -35,
        ]);
    }
}

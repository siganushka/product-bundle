<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Form\Type\ProductOptionCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductVariantCollectionType;
use Siganushka\ProductBundle\Model\ProductVariantChoiceGenerator;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                // 'constraints' => new NotBlank(),
                'required' => false,
                'priority' => 0,
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'constraints' => [
                    // new NotBlank(),
                    new Length(max: 255),
                ],
                'required' => false,
                'priority' => -10,
            ])
            ->add('summary', TextType::class, [
                'label' => 'product.summary',
                'constraints' => new Length(max: 255),
                'required' => false,
                'priority' => -20,
            ])
            ->add('options', ProductOptionCollectionType::class, [
                'label' => 'product.options',
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
            if (!$data instanceof AbstractProduct) {
                $event->setData($data = $this->repository->createNew());
            }

            foreach ($data->generateChoices() as $choice) {
                $data->addVariant($this->productVariantRepository->createNew($choice)->setEnabled(true));
            }
        });

        $builder->get('options')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm()->getParent();
            $data = $event->getData();

            if ($form && is_iterable($data)) {
                $product = $form->getData();
                if ($product instanceof AbstractProduct) {
                    $previousData = $product->getVariants()->toArray();
                    // reset original product variants index
                    $product->getVariants()->clear();
                } else {
                    $previousData = [];
                }

                /** @var ArrayCollection<int, AbstractProductVariant> */
                $newData = new ArrayCollection();
                foreach (ProductVariantChoiceGenerator::generate($data) as $choice) {
                    $newData[] = array_find($previousData, static fn (AbstractProductVariant $item) => $item->getCode() === $choice->code)
                        ?? $this->productVariantRepository->createNew($choice)->setEnabled(true);
                }

                $form->get('variants')->setData($newData);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
        ]);
    }
}

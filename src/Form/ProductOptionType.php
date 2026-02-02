<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesCollectionType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class ProductOptionType extends AbstractType
{
    public function __construct(private readonly ProductOptionRepository $repository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $options['simple']
            ? ProductOptionValuesTextType::class
            : ProductOptionValuesCollectionType::class;

        $builder
            ->add('name', TextType::class, [
                'label' => 'product_option.name',
                'row_attr' => false === $options['label'] ? ['class' => 'w-25'] : [],
                'constraints' => new NotBlank(),
            ])
            ->add('values', $type, [
                'label' => 'product_option.values',
                'row_attr' => false === $options['label'] ? ['class' => 'w-75'] : [],
                'constraints' => [
                    new Count(min: 1),
                    new Unique(normalizer: static fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value)),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
            'simple' => false,
        ]);
    }
}

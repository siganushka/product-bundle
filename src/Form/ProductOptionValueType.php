<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProductOptionValueType extends AbstractType
{
    public function __construct(private readonly ProductOptionValueRepository $repository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('img', MediaType::class, [
                'label' => 'product_option_value.img',
                'rule' => 'product_img',
                'style' => false === $options['label'] ? 'width: 38px; height: 38px' : null,
                'row_attr' => false === $options['label'] ? ['class' => 'w-0'] : [],
                'priority' => 0,
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'product_option_value.name',
                'constraints' => [
                    new NotBlank(),
                    new Regex('/'.preg_quote(ProductOptionValuesTextType::VALUES_DELIMITER).'/', match: false),
                ],
                'priority' => -10,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'channel' => 'product_option_value_img',
                // Attributes when embedded in a collection
                'style' => false === $options['label'] ? 'width: 38px; height: 38px' : null,
                'row_attr' => false === $options['label'] ? ['style' => 'width: 0'] : [],
            ])
            ->add('text', TextType::class, [
                'label' => 'product_option_value.text',
                'constraints' => new NotBlank(),
            ])
            ->add('note', TextType::class, [
                'label' => 'product_option_value.note',
                'constraints' => new Length(max: 100),
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

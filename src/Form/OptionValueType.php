<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\MediaBundle\Form\Type\MediaType;
use Siganushka\ProductBundle\Entity\OptionValue;
use Siganushka\ProductBundle\Media\OptionValueImg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OptionValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextType::class, [
                'label' => 'option.value.text',
                'constraints' => [
                    new NotBlank(),
                    new Length(null, null, 128),
                ],
            ])
            ->add('img', MediaType::class, [
                'label' => 'option.value.img',
                'channel' => OptionValueImg::class,
                'width' => '38px',
                'height' => '38px',
            ])
            ->add('sort', IntegerType::class, [
                'label' => 'option.value.sort',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OptionValue::class,
        ]);
    }
}

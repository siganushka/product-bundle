<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'option.name',
                // TextType 类型在初始设置了值（比如修改表单）之后，如果再次提交空值时，实际提交值将为 NULL
                // 因此将报如下错误：Expected argument of type "string", "null" given at property path
                // 解决办法有两个，一是将实体 setter 类型由 "string" 改为 "?string"（允许 NULL 值），二是显式
                // 指定表单选项 empty_data=''，在内部将由 ViewTransformer 将 null 值转为空字符串
                // @see https://symfony.com/doc/current/reference/forms/types/form.html#empty-data
                // @see https://symfony.com/doc/current/reference/forms/types/text.html#empty-data
                // @see https://github.com/symfony/symfony/pull/18357
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                    new Length(null, null, 128),
                ],
            ])
            ->add('values', CollectionType::class, [
                'label' => 'option.values',
                'entry_type' => OptionValueType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'by_reference' => false,
                'constraints' => [
                    new Count([
                        'min' => 2,
                        'minMessage' => 'option.values.count.invalid',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Option::class,
        ]);
    }
}

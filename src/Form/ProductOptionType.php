<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

use function Symfony\Component\String\u;

class ProductOptionType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product_option.name',
                'constraints' => new NotBlank(),
            ])
        ;

        if ($options['using_tagsinput']) {
            $this->addValueTagsinputField($builder);
        } else {
            $this->addValueCollectionField($builder);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
            'using_tagsinput' => false,
        ]);
    }

    private function addValueTagsinputField(FormBuilderInterface $builder): void
    {
        $builder->add('values', TextType::class, [
            'label' => 'product_option.values',
            'constraints' => [
                new Count(['min' => 2, 'minMessage' => 'product_option.values.min_count']),
                new Unique([
                    'message' => 'product_option.values.unique',
                    'normalizer' => fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value),
                ]),
            ],
        ]);

        $builder->get('values')->addModelTransformer($this);
    }

    private function addValueCollectionField(FormBuilderInterface $builder): void
    {
        $builder->add('values', CollectionType::class, [
            'label' => 'product_option.values',
            'entry_type' => ProductOptionValueType::class,
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'error_bubbling' => false,
            'by_reference' => false,
            // [important] Using nested collections
            'prototype_name' => '__PRODUCT_OPTION_VALUES__',
            'constraints' => [
                new Count(['min' => 2, 'minMessage' => 'product_option.values.min_count']),
                new Unique([
                    'message' => 'product_option.values.unique',
                    'normalizer' => fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value),
                ]),
            ],
        ]);
    }

    public function transform($value): ?string
    {
        if (!$value instanceof Collection) {
            return null;
        }

        $texts = $value->map(fn (ProductOptionValue $value) => $value->getText());

        return implode(', ', $texts->toArray());
    }

    public function reverseTransform($value): array
    {
        if (null === $value || u($value)->isEmpty()) {
            return [];
        }

        $texts = explode(',', $value);
        $texts = array_map('trim', $texts);
        $texts = array_unique($texts);
        $texts = array_filter($texts);

        return array_map(fn (string $text) => new ProductOptionValue($text), $texts);
    }
}

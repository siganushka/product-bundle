<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

use function Symfony\Component\String\u;

class ProductOptionValueCollectionType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'product_option.name',
                'constraints' => new NotBlank(),
            ])
            ->add('values', TextType::class, [
                'label' => 'product_option.values',
                'constraints' => [
                    new Count(['min' => 2, 'minMessage' => 'product_option.values.min_count']),
                    new Unique([
                        'message' => 'product_option.values.unique',
                        'normalizer' => fn (ProductOptionValue $value) => $value->getText() ?? spl_object_hash($value),
                    ]),
                ],
            ])
        ;

        $builder->get('values')->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
        ]);
    }

    public function transform($value): ?string
    {
        if (!$value instanceof Collection) {
            return null;
        }

        $texts = $value->map(fn (ProductOptionValue $productOptionValue) => $productOptionValue->getText());

        return implode(', ', $texts->toArray());
    }

    public function reverseTransform($value): array
    {
        if (null === $value || u($value)->isEmpty()) {
            return [];
        }

        $texts = u($value)->split(',');
        $texts = array_map('trim', $texts);
        $texts = array_unique($texts);
        $texts = array_filter($texts);

        return array_map(fn (string $text) => new ProductOptionValue($text), $texts);
    }
}

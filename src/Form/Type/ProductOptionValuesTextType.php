<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Siganushka\ProductBundle\Form\EventListener\ProductOptionValuesTextListener;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValuesTextType extends AbstractType
{
    public const VALUES_DELIMITER = ',';

    public function __construct(private readonly ProductOptionValueRepository $repository)
    {
    }

    /**
     * @param array{ delimiter: non-empty-string } $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ProductOptionValuesToStringTransformer($this->repository, self::VALUES_DELIMITER));
        $builder->addEventSubscriber(new ProductOptionValuesTextListener());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'autocomplete' => true,
            'tom_select_options' => [
                'create' => true,
                'createOnBlur' => true,
                'duplicates' => true,
                'delimiter' => self::VALUES_DELIMITER,
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}

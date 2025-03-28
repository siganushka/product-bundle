<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValuesTextType extends AbstractType
{
    public function __construct(private readonly ProductOptionValueRepository $repository)
    {
    }

    /**
     * @param array{ delimiter: non-empty-string } $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ProductOptionValuesToStringTransformer($this->repository, $options['delimiter']));
        $builder->addEventListener(FormEvents::SUBMIT, $this->onSubmit(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'delimiter' => ',',
            'autocomplete' => true,
            'tom_select_options' => fn (Options $options) => [
                'create' => true,
                'createOnBlur' => true,
                'duplicates' => true,
                'delimiter' => $options['delimiter'],
            ],
        ]);

        $resolver->setAllowedTypes('delimiter', 'string');
        $resolver->setNormalizer('delimiter', fn (Options $options, string $delimiter) => trim($delimiter));
    }

    public function onSubmit(SubmitEvent $event): void
    {
        /** @var array<int, ProductOptionValue> */
        $previousData = $event->getForm()->getData() ?? [];
        /** @var array<int, ProductOptionValue> */
        $newData = $event->getData();

        foreach ($newData as $key => $value) {
            foreach ($previousData as $previousValue) {
                if ($previousValue->getText() === $value->getText()) {
                    $newData[$key] = $previousValue;
                    break;
                }
            }
        }

        $event->setData($newData);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}

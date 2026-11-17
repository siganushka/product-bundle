<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Form\DataTransformer\ProductOptionValuesToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValuesTextType extends AbstractType
{
    public function __construct(private readonly ProductOptionValuesToStringTransformer $transformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this->transformer);
        $builder->addEventListener(FormEvents::SUBMIT, $this->onSubmit(...));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'autocomplete' => true,
            'tom_select_options' => [
                'create' => true,
                'delimiter' => ProductOptionValuesToStringTransformer::DEFAULT_SEPARATOR,
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function onSubmit(FormEvent $event): void
    {
        /** @var array<int, AbstractProductOptionValue> */
        $previousData = $event->getForm()->getNormData() ?? [];
        /** @var array<int, AbstractProductOptionValue> */
        $newData = $event->getData();

        foreach ($newData as $key => $value) {
            foreach ($previousData as $previousValue) {
                if ($previousValue->getName() === $value->getName()) {
                    $newData[$key] = $previousValue;
                    break;
                }
            }
        }

        $event->setData($newData);
    }
}

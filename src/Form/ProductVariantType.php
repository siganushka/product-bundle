<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Form\Type\ProductVariantChoiceType;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', CentsMoneyType::class, [
                'label' => 'product.variant.price',
                'constraints' => new NotBlank(),
            ])
            ->add('inventory', IntegerType::class, [
                'label' => 'product.variant.inventory',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                    new LessThanOrEqual(2147483647),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariant::class,
            'constraints' => new UniqueEntity([
                'fields' => ['product', 'choice'],
                'errorPath' => 'optionValues',
                'message' => 'product.variant.option_values.unique',
                'ignoreNull' => false,
            ]),
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof ProductVariant) {
            return;
        }

        $product = $data->getProduct();
        if (!$product instanceof Product) {
            return;
        }

        $usedChoices = $product->getVariants()->map(fn (ProductVariant $variant) => $variant->getChoice());

        // important!!!
        if ($data->getId()) {
            $usedChoices->removeElement($data->getChoice());
        }

        $placeholder = 'generic.choice';
        $constraints = new NotBlank();

        if ($product->getOptions()->isEmpty()) {
            $placeholder = 'product.variant.option_values.null';
            $constraints = [];
        }

        $form = $event->getForm();
        $form->add('optionValues', ProductVariantChoiceType::class, [
            'label' => 'product.variant.option_values',
            'choice_attr' => fn (OptionValueCollection $choice) => ['disabled' => $usedChoices->contains($choice->getValue())],
            'placeholder' => $placeholder,
            'constraints' => $constraints,
            'disabled' => $data->getId() ? true : false,
            'product' => $product,
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?OptionValueCollection $value): void {
                $value && $variant->setOptionValues($value);
            },
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
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
            ->add('price', MoneyType::class, [
                'label' => 'product.variant.price',
                'scale' => 2,
                'divisor' => 100,
                'currency' => false,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                    new LessThanOrEqual(2147483600),
                ],
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

        $options = $product->getOptions();
        if ($options->isEmpty()) {
            return;
        }

        $variants = $product->getVariants();
        $usedChoices = $variants->map(fn (ProductVariant $variant) => $variant->getOptionValues()->getValue());

        $form = $event->getForm();
        $form->add('optionValues', ChoiceType::class, [
            'label' => 'product.variant.option_values',
            'choices' => $product->getOptionValueChoices(),
            'choice_label' => function (OptionValueCollection $choice) use ($usedChoices) {
                $label = (string) $choice;

                return $usedChoices->contains($choice->getValue()) ? sprintf('%s (√)', $label) : $label;
            },
            'choice_value' => fn (OptionValueCollection $choice) => $choice->getValue(),
            'choice_attr' => fn (OptionValueCollection $choice) => ['disabled' => $usedChoices->contains($choice->getValue())],
            'choice_translation_domain' => false,
            'disabled' => $data->getId() ? true : false,
            'priority' => 1,
            'empty_data' => new OptionValueCollection(),
            'constraints' => new NotBlank(),
            'setter' => function (ProductVariant &$variant, OptionValueCollection $value): void {
                $variant->setOptionValues($value);
            },
        ]);
    }
}

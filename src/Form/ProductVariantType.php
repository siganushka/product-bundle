<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\ProductVariantChoiceType;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', MoneyType::class, [
                'label' => 'product.variant.price',
                'scale' => 2,
                'divisor' => 100,
                'currency' => 'CNY',
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
            'constraints' => new Callback([$this, 'validateChoice']),
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

        $form = $event->getForm();
        $form->add('optionValues', ProductVariantChoiceType::class, [
            'label' => 'product.variant.option_values',
            'choice_attr' => fn (OptionValueCollection $choice) => ['disabled' => $usedChoices->contains($choice->getValue())],
            'placeholder' => '---请选择---',
            'constraints' => $product->getOptions()->isEmpty() ? [] : new NotBlank(),
            'disabled' => $data->getId() ? true : false,
            'product' => $product,
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?OptionValueCollection $value): void {
                $value && $variant->setOptionValues($value);
            },
        ]);
    }

    public function validateChoice(ProductVariant $data, ExecutionContextInterface $context): void
    {
        $product = $data->getProduct();
        if (!$product instanceof Product) {
            return;
        }

        $usedChoices = $product->getVariants()->map(fn (ProductVariant $variant) => $variant->getChoice());

        // important!!!
        if ($data->getId()) {
            $usedChoices->removeElement($data->getChoice());
        }

        if ($usedChoices->contains($data->getChoice())) {
            $context->buildViolation('该产品库存已存在！')
                ->atPath('optionValues')
                ->addViolation()
            ;
        }
    }
}

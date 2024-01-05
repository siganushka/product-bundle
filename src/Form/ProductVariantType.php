<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form;

use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\Type\CentsMoneyType;
use Siganushka\ProductBundle\Form\Type\ProductVariantChoiceType;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Util\FormUtil;
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
            ->add('price', CentsMoneyType::class, [
                'label' => 'product.variant.price',
                'constraints' => new NotBlank(),
            ])
            ->add('inventory', IntegerType::class, [
                'label' => 'product.variant.inventory',
                'constraints' => [
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
            'constraints' => new Callback([$this, 'validate']),
        ]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $variant = $event->getData();
        if (!$variant instanceof ProductVariant) {
            return;
        }

        $product = $variant->getProduct();
        if (!$product instanceof Product) {
            return;
        }

        $options = $product->getOptions();
        if ($options->isEmpty()) {
            return;
        }

        $form = $event->getForm();
        $form->add('optionValues', ProductVariantChoiceType::class, [
            'label' => 'product.variant.option_values',
            'choice_attr' => fn (OptionValueCollection $choice) => ['disabled' => $this->checkChoiceIsUsed($variant, $choice)],
            'placeholder' => 'generic.choice',
            'constraints' => new NotBlank(),
            'disabled' => $variant->getId() ? true : false,
            'product' => $product,
            'priority' => 1,
            'setter' => function (ProductVariant &$variant, ?OptionValueCollection $value): void {
                $value && $variant->setOptionValues($value);
            },
        ]);
    }

    public function validate(?ProductVariant $variant, ExecutionContextInterface $context): void
    {
        if (null === $variant) {
            return;
        }

        if ($this->checkChoiceIsUsed($variant, $variant->getOptionValues())) {
            $context->buildViolation('product.variant.option_values.unique')
                ->atPath('optionValues')
                ->addViolation()
            ;
        }
    }

    public function checkChoiceIsUsed(ProductVariant $variant, OptionValueCollection $optionValues): bool
    {
        if ($variant->getChoice() === $optionValues->getValue()) {
            return false;
        }

        $product = $variant->getProduct();
        if (!$product instanceof Product) {
            return false;
        }

        $choices = $product->getVariants()
            ->map(fn (ProductVariant $item) => $item->getChoice())
            ->filter(fn (string $choiceAsString) => !FormUtil::isEmpty($choiceAsString))
        ;

        // important!!!
        // if ($variant->getId()) {
        //     $choices->removeElement($variant->getChoice());
        // }

        return $choices->contains($optionValues->getValue());
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class CentsMoneyType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'scale' => 2,
            'divisor' => 100,
            'currency' => 'CNY',
            'constraints' => [
                new GreaterThanOrEqual(0),
                new LessThanOrEqual(2147483600),
            ],
        ]);
    }

    public function getParent(): string
    {
        return MoneyType::class;
    }
}

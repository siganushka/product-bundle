<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CentsMoneyType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'scale' => 2,
            'divisor' => 100,
            'currency' => 'CNY',
        ]);
    }

    public function getParent(): string
    {
        return MoneyType::class;
    }
}

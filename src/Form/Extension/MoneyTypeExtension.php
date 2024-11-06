<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyTypeExtension extends AbstractTypeExtension
{
    public function __construct(private int $scale = 2, private int $divisor = 100, private string $currency = 'CNY')
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'scale' => $this->scale,
            'divisor' => $this->divisor,
            'currency' => $this->currency,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            MoneyType::class,
        ];
    }
}

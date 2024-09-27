<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class CentsMoneyType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setNormalizer('constraints', function (Options $options, mixed $constraints): array {
            $constraints = \is_object($constraints) ? [$constraints] : (array) $constraints;
            $constraints[] = new GreaterThanOrEqual($options['negative'] ? -2147483600 : 0);
            $constraints[] = new LessThanOrEqual(2147483600);

            return $constraints;
        });

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::CURRENCY);
        $currency = $formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
        // dump(\locale::getDefault(), $currency);

        $resolver->setDefaults([
            'scale' => 2,
            'divisor' => 100,
            'currency' => $currency,
            'negative' => false,
        ]);

        $resolver->setAllowedTypes('negative', 'bool');
    }

    public function getParent(): string
    {
        return MoneyType::class;
    }
}

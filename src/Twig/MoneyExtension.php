<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Twig;

use Siganushka\GenericBundle\Utils\CurrencyUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyExtension extends AbstractExtension
{
    private CurrencyUtils $currencyUtils;

    public function __construct(CurrencyUtils $currencyUtils)
    {
        $this->currencyUtils = $currencyUtils;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('money', [$this, 'moneyFilter']),
        ];
    }

    public function moneyFilter(?int $value, array $context = []): string
    {
        return $this->currencyUtils->format($value, $context);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Twig;

use Siganushka\ProductBundle\Formatter\MoneyFormatterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyExtension extends AbstractExtension
{
    private MoneyFormatterInterface $formatter;

    public function __construct(MoneyFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('money', [$this->formatter, 'format']),
        ];
    }
}

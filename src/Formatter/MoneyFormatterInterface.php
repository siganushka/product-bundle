<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Formatter;

/**
 * @see https://www.php.net/manual/en/function.number-format.php
 */
interface MoneyFormatterInterface
{
    public const DIVISOR = 'divisor';
    public const DECIMALS = 'decimals';
    public const DEC_POINT = 'dec_point';
    public const THOUSANDS_SEP = 'thousands_sep';

    /**
     * Format number to money.
     *
     * @param int|null                                                                           $number  The number to format
     * @param array{ divisor?: int, decimals?: int, dec_point?: string, thousands_sep?: string } $context The context to format money
     *
     * @return string Formatted money
     */
    public function format(?int $number, array $context = []): string;
}

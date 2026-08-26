<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;

/**
 * @template TProductOptionValue of AbstractProductOptionValue = AbstractProductOptionValue
 */
final class ProductVariantChoice
{
    /**
     * Generated unique code for combined option values.
     */
    public readonly ?string $code;

    /**
     * Generated name for combined option values.
     */
    public readonly ?string $name;

    /**
     * @param array<array-key, TProductOptionValue> $combinedOptionValues
     */
    public function __construct(public readonly array $combinedOptionValues = [])
    {
        $codes = $names = [];
        foreach ($combinedOptionValues as $optionValue) {
            $codes[] = $optionValue->getCode();
            $names[] = $optionValue->getName() ?? '-';
        }

        // [important] Generate sorted unique codes.
        sort($codes);

        $this->code = \count($codes) ? implode('-', $codes) : null;
        $this->name = \count($names) ? implode('/', $names) : null;
    }

    /**
     * @param TProductOptionValue ...$combinedOptionValues
     */
    public static function create(AbstractProductOptionValue ...$combinedOptionValues): static
    {
        return new static($combinedOptionValues);
    }
}

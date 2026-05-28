<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @template TProductOptionValue of ProductOptionValue = ProductOptionValue
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
        $codes = $texts = [];
        foreach ($combinedOptionValues as $optionValue) {
            $codes[] = $optionValue->getCode();
            $texts[] = $optionValue->getText() ?? '-';
        }

        // [important] Generate sorted unique codes.
        sort($codes);

        $this->code = \count($codes) ? implode('-', $codes) : null;
        $this->name = \count($texts) ? implode('/', $texts) : null;
    }

    /**
     * @param TProductOptionValue ...$combinedOptionValues
     */
    public static function create(ProductOptionValue ...$combinedOptionValues): static
    {
        return new static($combinedOptionValues);
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Siganushka\ProductBundle\Entity\ProductOptionValue;

final class ProductVariantChoice
{
    /**
     * Generated unique value for combined option values.
     */
    public readonly ?string $value;

    /**
     * Generated label for combined option values.
     */
    public readonly ?string $label;

    /**
     * @var array<int, ProductOptionValue>
     */
    public readonly array $combinedOptionValues;

    public function __construct(array $combinedOptionValues = [])
    {
        $value = $label = [];
        foreach ($combinedOptionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                throw new \UnexpectedValueException(\sprintf('Expected argument of type "%s", "%s" given.', ProductOptionValue::class, get_debug_type($optionValue)));
            }

            $value[] = $optionValue->getCode();
            $label[] = $optionValue->getDescriptor();
        }

        $this->value = static::generateValue($value);
        $this->label = static::generateLabel($label);

        $this->combinedOptionValues = $combinedOptionValues;
    }

    /**
     * Generate unique choice value for product variant.
     *
     * @param array<int, string> $identifiers
     */
    public static function generateValue(array $identifiers): ?string
    {
        if (0 === \count($identifiers)) {
            return null;
        }

        // [important] Generate unique choice value from sorted identifiers
        sort($identifiers);

        return implode('-', $identifiers);
    }

    /**
     * Generate choice label for product variant.
     *
     * @param array<int, string|null> $texts
     */
    public static function generateLabel(array $texts): ?string
    {
        if (0 === \count($texts)) {
            return null;
        }

        return implode(', ', $texts);
    }
}

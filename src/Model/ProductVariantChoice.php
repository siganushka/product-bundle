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
        $code = $descriptor = [];
        foreach ($combinedOptionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                throw new \UnexpectedValueException(\sprintf('Expected argument of type "%s", "%s" given.', ProductOptionValue::class, get_debug_type($optionValue)));
            }

            $code[] = $optionValue->getCode();
            $descriptor[] = $optionValue->getDescriptor();
        }

        // [important] Generate unique choice value from sorted code
        sort($code);

        $this->value = \count($code) ? implode('-', $code) : null;
        $this->label = \count($descriptor) ? implode(', ', $descriptor) : null;

        $this->combinedOptionValues = $combinedOptionValues;
    }
}

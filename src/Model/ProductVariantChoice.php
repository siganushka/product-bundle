<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Siganushka\ProductBundle\Entity\ProductOptionValue;

final class ProductVariantChoice
{
    /**
     * Generated unique value for combined option values.
     */
    public ?string $value;

    /**
     * Generated label for combined option values.
     */
    public ?string $label;

    /**
     * @var array<int, ProductOptionValue>
     */
    public array $combinedOptionValues;

    public function __construct(array $combinedOptionValues = [])
    {
        $value = $label = [];
        foreach ($combinedOptionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given.', ProductOptionValue::class, get_debug_type($optionValue)));
            }

            // Using id for persisted identifier
            $identifier = $optionValue->getId();

            $value[] = null === $identifier ? spl_object_hash($optionValue) : sprintf('%07d', $identifier);
            $label[] = $optionValue->getDescriptor();
        }

        // [important] Generate unique identity from sorted value
        sort($value);

        $this->value = \count($value) ? implode('-', $value) : null;
        $this->label = \count($label) ? implode(', ', $label) : null;

        $this->combinedOptionValues = $combinedOptionValues;
    }
}

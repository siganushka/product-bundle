<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Siganushka\ProductBundle\Entity\ProductOptionValue;

final class ProductVariantChoice
{
    public ?string $value;
    public ?string $label;

    /**
     * @var array<int, ProductOptionValue>
     */
    public array $optionValues;

    public function __construct(array $optionValues = [])
    {
        $value = $label = [];
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given.', ProductOptionValue::class, get_debug_type($optionValue)));
            }

            $value[] = $optionValue->getId() ? sprintf('%07d', $optionValue->getId()) : spl_object_hash($optionValue);
            $label[] = $optionValue->getDescriptor();
        }

        // [important] Generate unique identity from sorted value
        sort($value);

        $this->value = \count($value) ? implode('-', $value) : null;
        $this->label = \count($label) ? implode(', ', $label) : null;

        $this->optionValues = $optionValues;
    }
}

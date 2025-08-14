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
     * @var ProductOptionValue[]
     */
    public readonly array $combinedOptionValues;

    public function __construct(ProductOptionValue ...$combinedOptionValues)
    {
        $codes = $texts = [];
        foreach ($combinedOptionValues as $optionValue) {
            $codes[] = $optionValue->getCode() ?? spl_object_id($optionValue);
            $texts[] = $optionValue->getText() ?? '-';
        }

        // [important] Generate unique choice value from sorted code
        sort($codes);

        $this->value = \count($codes) ? implode('-', $codes) : null;
        $this->label = \count($texts) ? implode('/', $texts) : null;

        $this->combinedOptionValues = $combinedOptionValues;
    }
}

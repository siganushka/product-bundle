<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @extends ArrayCollection<array-key, ProductOptionValue>
 */
final class ProductVariantChoice extends ArrayCollection
{
    /**
     * Generated unique choice value for combined option values.
     */
    public readonly ?string $value;

    /**
     * Generated choice label for combined option values.
     */
    public readonly ?string $label;

    /**
     * @param array<array-key, ProductOptionValue> $combinedOptionValues
     */
    public function __construct(array $combinedOptionValues = [])
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

        parent::__construct($combinedOptionValues);
    }

    public static function create(ProductOptionValue ...$combinedOptionValues): static
    {
        return new static($combinedOptionValues);
    }
}

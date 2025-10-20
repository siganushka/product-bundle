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
     * Generated unique code for combined option values.
     */
    public readonly ?string $code;

    /**
     * Generated name for combined option values.
     */
    public readonly ?string $name;

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

        // [important] Generated unique code by sorted
        sort($codes);

        $this->code = \count($codes) ? implode('-', $codes) : null;
        $this->name = \count($texts) ? implode('/', $texts) : null;

        parent::__construct($combinedOptionValues);
    }

    public static function create(ProductOptionValue ...$combinedOptionValues): static
    {
        return new static($combinedOptionValues);
    }
}

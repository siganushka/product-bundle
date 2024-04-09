<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @template-extends ArrayCollection<int, ProductOptionValue>
 */
class ProductVariantChoice extends ArrayCollection
{
    public ?string $value;
    public ?string $label;

    /**
     * @param array<int, ProductOptionValue|null> $optionValues
     */
    public function __construct(array $optionValues = [])
    {
        // initialize before
        parent::__construct();

        $value = $label = [];
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                continue;
            }

            $value[] = $optionValue->getId() ?? spl_object_hash($optionValue);
            $label[] = $optionValue->getDescriptor();

            // Add to elements
            $this->add($optionValue);
        }

        // [important] Generate identity from sorted value
        sort($value);

        $this->value = \count($value) ? implode('-', $value) : null;
        $this->label = \count($label) ? implode(', ', $label) : null;
    }
}

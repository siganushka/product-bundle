<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @template-extends ArrayCollection<int, ProductOptionValue>
 */
class CombinedOptionValues extends ArrayCollection
{
    public ?string $value;
    public ?string $label;

    public function __construct(array $optionValues = [])
    {
        $value = $label = [];
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given', ProductOptionValue::class, get_debug_type($optionValue)));
            }

            $value[] = $optionValue->getId() ?? spl_object_hash($optionValue);
            $label[] = $optionValue->getDescriptor();
        }

        // [important] Generate identity from sorted value
        sort($value);

        $this->value = \count($value) ? implode('-', $value) : null;
        $this->label = \count($label) ? implode(', ', $label) : null;

        parent::__construct($optionValues);
    }

    public function equalsTo(?self $target): bool
    {
        if (null === $target) {
            return false;
        }

        return $this->value === $target->value;
    }
}

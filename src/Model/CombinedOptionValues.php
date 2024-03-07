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
    private ?string $label;
    private ?string $value;

    public function __construct(array $optionValues = [])
    {
        $label = $value = [];
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof ProductOptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given', ProductOptionValue::class, get_debug_type($optionValue)));
            }

            $label[] = $optionValue->getDescriptor();
            $value[] = $optionValue->getCode();
        }

        // [important] Generate identity from sorted value
        sort($value);

        $this->label = \count($label) ? implode('/', $label) : null;
        $this->value = \count($value) ? implode('-', $value) : null;

        parent::__construct($optionValues);
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function equalsTo(?self $target): bool
    {
        if (null === $target) {
            return false;
        }

        return $this->value === $target->getValue();
    }
}

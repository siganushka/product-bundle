<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\OptionValue;

/**
 * @template-extends ArrayCollection<int, OptionValue>
 */
class CombinedOptionValues extends ArrayCollection
{
    private ?string $label;
    private ?string $value;

    public function __construct(array $optionValues = [])
    {
        $label = $value = [];
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof OptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given', OptionValue::class, get_debug_type($optionValue)));
            }

            $label[] = $optionValue->getText();
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

    public function equalsTo(self $target): bool
    {
        return $this->value === $target->getValue();
    }
}

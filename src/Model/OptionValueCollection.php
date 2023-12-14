<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\OptionValue;

/**
 * @template-extends ArrayCollection<int, mixed>
 */
class OptionValueCollection extends ArrayCollection
{
    private string $label;

    private string $value;

    public function __construct(array $optionValues = [])
    {
        $label = $value = [];
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof OptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given', OptionValue::class, get_debug_type($optionValue)));
            }

            $label[] = $optionValue->getText();
            $value[] = $optionValue->getId();
        }

        // important!
        // sort($value);

        $this->label = implode('/', $label);
        $this->value = implode('_', $value);

        parent::__construct($optionValues);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}

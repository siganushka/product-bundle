<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\OptionValue;

class OptionValueCollection extends ArrayCollection
{
    public function __construct(array $optionValues = [])
    {
        foreach ($optionValues as $optionValue) {
            if (!$optionValue instanceof OptionValue) {
                throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given', OptionValue::class, get_debug_type($optionValue)));
            }
        }

        parent::__construct($optionValues);
    }

    public function createChoiceLabel(): string
    {
        return implode('/', array_map(fn (OptionValue $optionValue) => $optionValue->getText(), $this->toArray()));
    }

    public function createChoiceValue(): string
    {
        return implode('/', array_map(fn (OptionValue $optionValue) => $optionValue->getCode(), $this->toArray()));
    }
}

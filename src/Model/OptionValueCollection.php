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
    public function getChoiceKey(): string
    {
        return implode('_', array_map(fn (OptionValue $value) => $value->getCode(), $this->toArray()));
    }

    public function __toString(): string
    {
        return implode('/', array_map(fn (OptionValue $value) => $value->getText(), $this->toArray()));
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @extends ArrayCollection<array-key, ProductOptionValue>
 */
final class CombinedOptionValueCollection extends ArrayCollection
{
    /**
     * Generated unique code for combined option values.
     */
    public readonly ?string $code;

    /**
     * Generated text for combined option values.
     */
    public readonly ?string $text;

    public function __construct(ProductOptionValue ...$combinedOptionValues)
    {
        $codes = $texts = [];
        foreach ($combinedOptionValues as $optionValue) {
            $codes[] = $optionValue->getCode() ?? spl_object_id($optionValue);
            $texts[] = $optionValue->getText() ?? '-';
        }

        // [important] Generate unique choice value from sorted code
        sort($codes);

        $this->code = \count($codes) ? implode('-', $codes) : null;
        $this->text = \count($texts) ? implode('/', $texts) : null;

        parent::__construct($combinedOptionValues);
    }

    /**
     * @param array|Collection<array-key, ProductOptionValue> $combinedOptionValues
     */
    public static function create(array|Collection $combinedOptionValues): static
    {
        if ($combinedOptionValues instanceof Collection) {
            $combinedOptionValues = iterator_to_array($combinedOptionValues);
        }

        return new static(...$combinedOptionValues);
    }
}

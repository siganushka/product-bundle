<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Model;

use BenTools\CartesianProduct\CartesianProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;

class ProductVariantChoiceGenerator
{
    /**
     * @param iterable<int, AbstractProductOption> $options
     *
     * @return array<int, ProductVariantChoice>
     */
    public static function generate(iterable $options): array
    {
        $set = [];
        foreach ($options as $option) {
            $values = $option->getValues();
            if ($values->count()) {
                $set[] = $values;
            }
        }

        if (0 === \count($set)) {
            return [new ProductVariantChoice()];
        }

        $cartesianProduct = new CartesianProduct($set);
        $asArray = $cartesianProduct->asArray();

        return array_map(static fn (array $combinedOptionValues) => new ProductVariantChoice($combinedOptionValues), $asArray);
    }
}

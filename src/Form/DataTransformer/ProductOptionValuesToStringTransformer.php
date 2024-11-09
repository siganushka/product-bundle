<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Form\DataTransformer;

use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function Symfony\Component\String\u;

/**
 * @implements DataTransformerInterface<mixed, mixed>
 */
class ProductOptionValuesToStringTransformer implements DataTransformerInterface
{
    /**
     * @param non-empty-string $separator
     */
    public function __construct(protected readonly string $separator)
    {
    }

    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (!\is_array($value)) {
            throw new TransformationFailedException('Expected an array or Traversable.');
        }

        return implode($this->separator, array_map(fn (ProductOptionValue $item) => $item->getText(), $value));
    }

    public function reverseTransform(mixed $value): array
    {
        if (null !== $value && !\is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if (null === $value || u($value)->isEmpty()) {
            return [];
        }

        $texts = explode($this->separator, $value);
        $texts = array_map('trim', $texts);
        $texts = array_filter($texts);

        return array_map(fn (string $text) => new ProductOptionValue(null, $text), $texts);
    }
}

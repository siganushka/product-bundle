<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Serializer\Normalizer;

use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductVariantChoiceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param ProductVariantChoice|mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): ?array
    {
        if (!$object instanceof ProductVariantChoice) {
            return null;
        }

        return [
            'label' => $object->getLabel(),
            'value' => $object->getValue(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof ProductVariantChoice;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

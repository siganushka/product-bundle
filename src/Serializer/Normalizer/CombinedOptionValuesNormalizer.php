<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Serializer\Normalizer;

use Siganushka\ProductBundle\Model\CombinedOptionValues;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CombinedOptionValuesNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @param CombinedOptionValues|mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): ?array
    {
        if (!$object instanceof CombinedOptionValues) {
            return null;
        }

        return [
            'label' => $object->getLabel(),
            'value' => $object->getValue(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof CombinedOptionValues;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

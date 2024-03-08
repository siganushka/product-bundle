<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Media;

use Siganushka\MediaBundle\AbstractChannel;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Mapping\GenericMetadata;

class ProductVariantImg extends AbstractChannel
{
    protected function loadConstraints(GenericMetadata $metadata): void
    {
        $constraint = new Image();
        $constraint->mimeTypes = ['image/*'];

        $metadata->addConstraint($constraint);
    }
}

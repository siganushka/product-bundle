<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Media;

use Siganushka\MediaBundle\AbstractChannel;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Mapping\GenericMetadata;

class ProductImg extends AbstractChannel
{
    protected function loadConstraints(GenericMetadata $metadata): void
    {
        $constraint = new Image();
        $constraint->mimeTypes = ['image/png', 'image/jpeg'];

        $metadata->addConstraint($constraint);
    }
}

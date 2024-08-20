<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Media;

use Siganushka\MediaBundle\AbstractResizeImageChannel;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Mapping\GenericMetadata;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ProductImg extends AbstractResizeImageChannel
{
    public function __construct()
    {
        parent::__construct(maxWidth: 800, maxHeight: 800);
    }

    protected function loadConstraints(GenericMetadata $metadata): void
    {
        /**
         * 图片必需为正方形，并且尺寸不能小于 minWidth.
         *
         * @see https://symfony.com/doc/5.x/reference/constraints/Image.html
         */
        $constraint = new Image();
        $constraint->minWidth = 100;
        $constraint->minWidthMessage = '_img.min_width.invalid';
        $constraint->allowSquare = true;
        $constraint->allowLandscape = false;
        $constraint->allowPortrait = false;
        $constraint->mimeTypes = ['image/png', 'image/jpeg', 'image/webp'];

        $constraint->allowSquareMessage =
        $constraint->allowLandscapeMessage =
        $constraint->allowPortraitMessage = '_img.square.invalid';

        $metadata->addConstraint($constraint);
    }
}

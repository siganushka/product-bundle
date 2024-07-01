<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Media;

use Siganushka\GenericBundle\Event\ResizeImageEvent;
use Siganushka\MediaBundle\AbstractChannel;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Mapping\GenericMetadata;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductImg extends AbstractChannel
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onPreSave(File $file): void
    {
        $this->eventDispatcher->dispatch(new ResizeImageEvent($file, 1000));
    }

    protected function loadConstraints(GenericMetadata $metadata): void
    {
        /**
         * 图片必需为正方形，并且尺寸不能小于 minWidth.
         *
         * @see https://symfony.com/doc/5.x/reference/constraints/Image.html
         */
        $constraint = new Image();
        $constraint->minWidth = 50;
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

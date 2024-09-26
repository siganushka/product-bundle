<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Media;

use Siganushka\GenericBundle\Event\ResizeImageEvent;
use Siganushka\MediaBundle\AbstractChannel;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductImg extends AbstractChannel
{
    public function __construct(protected readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function onPreSave(File $file): void
    {
        $this->eventDispatcher->dispatch(new ResizeImageEvent($file, 800, 800));
    }

    public function getConstraint(): Constraint
    {
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

        return $constraint;
    }
}

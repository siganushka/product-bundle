<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine\EventListener;

use Siganushka\ProductBundle\Entity\OptionValue;

class OptionValueRemoveListener
{
    public function preRemove(OptionValue $entity): void
    {
        $variants = $entity->getVariants();
        if ($variants->isEmpty()) {
            return;
        }

        throw new \RuntimeException('Unable to remove entity with relationships.');
    }
}

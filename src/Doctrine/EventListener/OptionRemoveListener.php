<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine\EventListener;

use Siganushka\ProductBundle\Entity\Option;

class OptionRemoveListener
{
    public function preRemove(Option $entity): void
    {
        $products = $entity->getProducts();
        if ($products->isEmpty()) {
            return;
        }

        throw new \RuntimeException('Unable to remove entity with relationships.');
    }
}

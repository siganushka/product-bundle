<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;

class ProductVariantUpdateListener
{
    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        $pendingEntities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
        );

        /** @var \SplObjectStorage<AbstractProduct, null> */
        $pendingProducts = new \SplObjectStorage();
        foreach ($pendingEntities as $entity) {
            if ($entity instanceof AbstractProductVariant && $entity->getProduct()) {
                $pendingProducts->attach($entity->getProduct());
            }
        }

        foreach ($pendingProducts as $product) {
            $prices = [];
            foreach ($product->getVariants() as $variant) {
                if ($variant->isEnabled() && null !== $variant->getPrice()) {
                    $prices[] = $variant->getPrice();
                }
            }

            $product->setLowestPrice($prices ? min($prices) : null);
            $product->setHighestPrice($prices ? max($prices) : null);

            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($product::class), $product);
        }
    }
}

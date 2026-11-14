<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;

class ProductPriceUpdateListener
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

        // The owning side of a pending variant may already be cleared (e.g. AbstractProduct::removeVariant()),
        // in this case the affected products can only be resolved from the changed variant collections.
        $pendingCollections = array_merge(
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions(),
        );

        foreach ($pendingCollections as $collection) {
            $owner = $collection->getOwner();
            if ($owner instanceof AbstractProduct) {
                $pendingProducts->attach($owner);
            }
        }

        foreach ($pendingProducts as $product) {
            // Skip recalculation if the product is detached, unmanaged, or scheduled for deletion
            // to prevent "Entity is not managed" exceptions during the flush phase.
            if (!$em->contains($product)) {
                continue;
            }

            $prices = [];
            foreach ($product->getVariants() as $variant) {
                // Variants scheduled for deletion no longer contribute to the price range.
                if ($uow->isScheduledForDelete($variant)) {
                    continue;
                }

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

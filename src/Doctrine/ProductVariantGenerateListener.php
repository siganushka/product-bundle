<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductVariantGenerateListener
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        /** @var \SplObjectStorage<AbstractProduct, null> */
        $productForVariants = new \SplObjectStorage();
        /** @var \SplObjectStorage<AbstractProduct, null> */
        $productForPrices = new \SplObjectStorage();

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        $pendingEntities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions(),
        );

        foreach ($pendingEntities as $entity) {
            if ($entity instanceof AbstractProductVariant && $entity->getProduct()) {
                $productForPrices->attach($entity->getProduct());
                continue;
            }

            if ($entity instanceof PersistentCollection) {
                $entity = $entity->getOwner();
            }

            $product = match (true) {
                $entity instanceof AbstractProduct => $entity,
                $entity instanceof AbstractProductOption => $entity->getProduct(),
                $entity instanceof AbstractProductOptionValue => $entity->getOption()?->getProduct(),
                default => null,
            };

            $product && $productForVariants->attach($product);
        }

        foreach ($productForVariants as $product) {
            $codes = [];
            foreach ($product->generateChoices() as $choice) {
                $codes[] = $choice->code;
                $product->addVariant($variant = $this->repository->createNew($choice));
            }

            foreach ($product->getVariants() as $variant) {
                if (!\in_array($variant->getCode(), $codes)) {
                    $product->removeVariant($variant);
                    $em->remove($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($product::class), $product);
        }

        foreach ($productForPrices as $product) {
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

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductVariantGenerateListener
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        /** @var \SplObjectStorage<AbstractProduct, null> */
        $products = new \SplObjectStorage();

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        $pendingEntities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
        );

        foreach (array_merge(
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions(),
        ) as $collection) {
            $pendingEntities[] = $collection->getOwner();
        }

        foreach ($pendingEntities as $entity) {
            $product = match (true) {
                $entity instanceof AbstractProduct => $entity,
                $entity instanceof AbstractProductOption => $entity->getProduct(),
                $entity instanceof AbstractProductOptionValue => $entity->getOption()?->getProduct(),
                default => null,
            };

            $product && $products->attach($product);
        }

        foreach ($products as $product) {
            $codes = [];
            foreach ($product->generateChoices() as $choice) {
                $codes[] = $choice->code;
                $product->addVariant($this->repository->createNew($choice)->setEnabled(false));
            }

            foreach ($product->getVariants()->toArray() as $variant) {
                if (!\in_array($variant->getCode(), $codes)) {
                    $product->removeVariant($variant);
                    $uow->scheduleForDelete($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($product::class), $product);
        }
    }
}

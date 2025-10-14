<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerInterface;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductVariantRepository $repository)
    {
    }

    public function __invoke(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        $changedProducts = [];
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Product) {
                $changedProducts[] = $entity;
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();
            $mappig = $collection->getMapping();
            if ($owner instanceof Product && is_subclass_of($mappig->targetEntity, ProductOption::class)) {
                $changedProducts[] = $owner;
            } elseif ($owner instanceof ProductOption && is_subclass_of($mappig->targetEntity, ProductOptionValue::class)) {
                $changedProducts[] = $owner->getProduct();
            }
        }

        $generatedProducts = [];
        foreach ($changedProducts as $entity) {
            if (null === $entity || \in_array($entity, $generatedProducts, true)) {
                continue;
            }

            $choices = $entity->generateChoices();
            $this->logger->info('Generated product variant choices.', [
                'product' => $entity->getName(),
                'choices' => array_map(fn (ProductVariantChoice $item) => $item->label, $choices),
            ]);

            foreach ($choices as $choice) {
                $entity->addVariant($this->repository->createNew($choice)->setEnabled(false));
            }

            foreach ($entity->getVariants() as $variant) {
                if (!\in_array($variant->getChoice(), $choices)) {
                    $em->remove($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
            $generatedProducts[] = $entity;
        }
    }
}

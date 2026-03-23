<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductListener
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        /** @var \SplObjectStorage<Product, null> */
        $collectProducts = new \SplObjectStorage();
        /** @var \SplObjectStorage<ProductVariant, null> */
        $collectProductVariants = new \SplObjectStorage();

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Product) {
                $collectProducts->attach($entity);
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();
            $mappig = $collection->getMapping();
            if ($owner instanceof Product && is_subclass_of($mappig->targetEntity, ProductOption::class)) {
                $collectProducts->attach($owner);
            } elseif ($owner instanceof ProductOption && is_subclass_of($mappig->targetEntity, ProductOptionValue::class) && $owner->getProduct()) {
                $collectProducts->attach($owner->getProduct());
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ProductOptionValue && \array_key_exists('text', $uow->getEntityChangeSet($entity))) {
                foreach ($entity->getVariants() as $variant) {
                    $collectProductVariants->attach($variant);
                }
            }
        }

        $this->generateProductVariants($em, $uow, $collectProducts);
        $this->updateProductVariants($em, $uow, $collectProductVariants);
    }

    /**
     * @param \SplObjectStorage<Product, null> $collectProducts
     */
    public function generateProductVariants(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $collectProducts): void
    {
        foreach ($collectProducts as $entity) {
            $codes = [];
            foreach ($entity->generateChoices() as $choice) {
                $codes[] = $choice->code;
                $entity->addVariant($this->repository->createNew($choice)->setEnabled(false));
            }

            foreach ($entity->getVariants() as $variant) {
                if (!\in_array($variant->getCode(), $codes)) {
                    $em->remove($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
        }
    }

    /**
     * @param \SplObjectStorage<ProductVariant, null> $collectProductVariants
     */
    public function updateProductVariants(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $collectProductVariants): void
    {
        foreach ($collectProductVariants as $entity) {
            $choice = $entity->getOptionValues();
            if (!$choice instanceof ProductVariantChoice) {
                $choice = new ProductVariantChoice($choice->toArray());
            }

            $ref = new \ReflectionProperty($entity, 'name');
            $ref->setValue($entity, $choice->name);

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
        }
    }
}

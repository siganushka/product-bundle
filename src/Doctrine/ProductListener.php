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
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductListener
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        /** @var \SplObjectStorage<Product, null> */
        $pendingToGenerateVariants = new \SplObjectStorage();
        /** @var \SplObjectStorage<Product, null> */
        $pendingToUpdatePriceRange = new \SplObjectStorage();
        /** @var \SplObjectStorage<ProductVariant, null> */
        $pendingToUpdateName = new \SplObjectStorage();

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Product) {
                $pendingToGenerateVariants->attach($entity);
            } elseif ($entity instanceof ProductVariant && $entity->getProduct()) {
                $pendingToUpdatePriceRange->attach($entity->getProduct());
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ProductOptionValue && \array_key_exists('text', $uow->getEntityChangeSet($entity))) {
                foreach ($entity->getVariants() as $variant) {
                    $pendingToUpdateName->attach($variant);
                }
            }

            if ($entity instanceof ProductVariant && $entity->getProduct()) {
                $pendingToUpdatePriceRange->attach($entity->getProduct());
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();
            $mappig = $collection->getMapping();
            if ($owner instanceof Product && is_subclass_of($mappig->targetEntity, ProductOption::class)) {
                $pendingToGenerateVariants->attach($owner);
            } elseif ($owner instanceof ProductOption && is_subclass_of($mappig->targetEntity, ProductOptionValue::class) && $owner->getProduct()) {
                $pendingToGenerateVariants->attach($owner->getProduct());
            }
        }

        $this->generateProductVariants($em, $uow, $pendingToGenerateVariants);
        $this->updateProductPriceRange($em, $uow, $pendingToUpdatePriceRange);
        $this->updateProductVariantName($em, $uow, $pendingToUpdateName);
    }

    /**
     * @param \SplObjectStorage<Product, null> $products
     */
    public function generateProductVariants(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $products): void
    {
        foreach ($products as $product) {
            $codes = [];
            foreach ($product->generateChoices() as $choice) {
                $codes[] = $choice->code;
                $product->addVariant($this->repository->createNew($choice)->setEnabled(false));
            }

            foreach ($product->getVariants() as $variant) {
                if (!\in_array($variant->getCode(), $codes)) {
                    $em->remove($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($product::class), $product);
        }
    }

    /**
     * @param \SplObjectStorage<Product, null> $products
     */
    public function updateProductPriceRange(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $products): void
    {
        foreach ($products as $product) {
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

    /**
     * @param \SplObjectStorage<ProductVariant, null> $variants
     */
    public function updateProductVariantName(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $variants): void
    {
        foreach ($variants as $variant) {
            $ref = new \ReflectionProperty($variant, 'name');
            $ref->setValue($variant, $variant->getChoice()->name);

            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($variant::class), $variant);
        }
    }
}

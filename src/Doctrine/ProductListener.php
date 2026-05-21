<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
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
        $updateProductVariants = new \SplObjectStorage();
        /** @var \SplObjectStorage<Product, null> */
        $updateProductPrice = new \SplObjectStorage();
        /** @var \SplObjectStorage<ProductVariant, null> */
        $updateVariantName = new \SplObjectStorage();

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Product) {
                $updateProductVariants->attach($entity);
            } elseif ($entity instanceof ProductVariant && $entity->getProduct()) {
                $updateProductPrice->attach($entity->getProduct());
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ProductVariant && $entity->getProduct()) {
                $updateProductPrice->attach($entity->getProduct());
            } elseif ($entity instanceof ProductOptionValue && \array_key_exists('text', $uow->getEntityChangeSet($entity))) {
                foreach ($entity->getVariants() as $variant) {
                    $updateVariantName->attach($variant);
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof ProductVariant && $entity->getProduct()) {
                $updateProductPrice->attach($entity->getProduct());
            } elseif ($entity instanceof ProductOptionValue && $product = $entity->getOption()?->getProduct()) {
                $updateProductVariants->attach($product);
            }
        }

        $collections = array_merge(
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions(),
        );

        foreach ($collections as $collection) {
            $owner = $collection->getOwner();
            $mappig = $collection->getMapping();
            if ($owner instanceof Product && is_subclass_of($mappig->targetEntity, ProductOption::class)) {
                $updateProductVariants->attach($owner);
            } elseif ($owner instanceof ProductOption && is_subclass_of($mappig->targetEntity, ProductOptionValue::class) && $owner->getProduct()) {
                $updateProductVariants->attach($owner->getProduct());
            }
        }

        foreach ($updateProductVariants as $product) {
            $this->updateProductVariants($product);
            $uow->computeChangeSet($em->getClassMetadata($product::class), $product);
        }

        foreach ($updateProductPrice as $product) {
            $this->updateProductPrice($product);
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($product::class), $product);
        }

        foreach ($updateVariantName as $variant) {
            $this->updateVariantName($variant);
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($variant::class), $variant);
        }
    }

    public function updateProductVariants(Product $product): void
    {
        $codes = [];
        foreach ($product->generateChoices() as $choice) {
            $codes[] = $choice->code;
            $product->addVariant($this->repository->createNew($choice)->setEnabled(false));
        }

        foreach ($product->getVariants() as $variant) {
            if (!\in_array($variant->getCode(), $codes)) {
                $product->removeVariant($variant);
            }
        }
    }

    public function updateProductPrice(Product $product): void
    {
        $prices = [];
        foreach ($product->getVariants() as $variant) {
            if ($variant->isEnabled() && null !== $variant->getPrice()) {
                $prices[] = $variant->getPrice();
            }
        }

        $product->setLowestPrice($prices ? min($prices) : null);
        $product->setHighestPrice($prices ? max($prices) : null);
    }

    public function updateVariantName(ProductVariant $variant): void
    {
        $choice = new ProductVariantChoice($variant->getOptionValues()->toArray());

        $ref = new \ReflectionProperty($variant, 'name');
        $ref->setValue($variant, $choice->name);
    }
}

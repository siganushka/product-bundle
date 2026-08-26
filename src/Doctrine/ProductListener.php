<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Entity\AbstractProductOption;
use Siganushka\ProductBundle\Entity\AbstractProductOptionValue;
use Siganushka\ProductBundle\Entity\AbstractProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductListener
{
    public function __construct(private readonly ProductVariantRepository $repository)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        /** @var \SplObjectStorage<AbstractProduct, null> */
        $updateProductVariants = new \SplObjectStorage();
        /** @var \SplObjectStorage<AbstractProduct, null> */
        $updateProductPrice = new \SplObjectStorage();
        /** @var \SplObjectStorage<AbstractProductVariant, null> */
        $updateVariantName = new \SplObjectStorage();

        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof AbstractProduct) {
                $updateProductVariants->attach($entity);
            } elseif ($entity instanceof AbstractProductVariant && $entity->getProduct()) {
                $updateProductPrice->attach($entity->getProduct());
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof AbstractProductVariant && $entity->getProduct()) {
                $updateProductPrice->attach($entity->getProduct());
            } elseif ($entity instanceof AbstractProductOptionValue && \array_key_exists('name', $uow->getEntityChangeSet($entity))) {
                foreach ($entity->getVariants() as $variant) {
                    $updateVariantName->attach($variant);
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof AbstractProductVariant && $entity->getProduct()) {
                $updateProductPrice->attach($entity->getProduct());
            } elseif ($entity instanceof AbstractProductOptionValue && $product = $entity->getOption()?->getProduct()) {
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
            if ($owner instanceof AbstractProduct && is_subclass_of($mappig->targetEntity, AbstractProductOption::class)) {
                $updateProductVariants->attach($owner);
            } elseif ($owner instanceof AbstractProductOption && is_subclass_of($mappig->targetEntity, AbstractProductOptionValue::class) && $owner->getProduct()) {
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

    public function updateProductVariants(AbstractProduct $product): void
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

    public function updateProductPrice(AbstractProduct $product): void
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

    public function updateVariantName(AbstractProductVariant $variant): void
    {
        $choice = new ProductVariantChoice($variant->getOptionValues()->toArray());

        $ref = new \ReflectionProperty($variant, 'name');
        $ref->setValue($variant, $choice->name);
    }
}

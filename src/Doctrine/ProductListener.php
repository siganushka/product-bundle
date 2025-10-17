<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Psr\Log\LoggerInterface;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductVariantRepository $repository)
    {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        /** @var \SplObjectStorage<Product, null> */
        $changedProducts = new \SplObjectStorage();
        /** @var \SplObjectStorage<ProductVariant, null> */
        $changedProductVariants = new \SplObjectStorage();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Product) {
                $changedProducts->attach($entity);
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();
            $mappig = $collection->getMapping();
            if ($owner instanceof Product && is_subclass_of($mappig->targetEntity, ProductOption::class)) {
                $changedProducts->attach($owner);
            } elseif ($owner instanceof ProductOption && is_subclass_of($mappig->targetEntity, ProductOptionValue::class) && $owner->getProduct()) {
                $changedProducts->attach($owner->getProduct());
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ProductOptionValue && \array_key_exists('text', $uow->getEntityChangeSet($entity))) {
                foreach ($entity->getVariants() as $variant) {
                    $changedProductVariants->attach($variant);
                }
            }
        }

        $this->generateProductVariants($em, $uow, $changedProducts);
        $this->updateProductVariants($em, $uow, $changedProductVariants);
    }

    /**
     * @param \SplObjectStorage<Product, null> $changedProducts
     */
    public function generateProductVariants(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $changedProducts): void
    {
        foreach ($changedProducts as $entity) {
            $choices = $entity->generateChoices();
            $this->logger->info('Generated product variant choices.', [
                'product' => $entity->getName(),
                'choices' => array_map(fn (ProductVariantChoice $item) => $item->label, $choices),
            ]);

            foreach ($choices as $choice) {
                $entity->addVariant($this->repository->createNew($choice)->setEnabled(false));
            }

            $choiceValues = array_map(fn (ProductVariantChoice $item) => $item->value, $choices);
            foreach ($entity->getVariants() as $variant) {
                if (!\in_array($variant->getValue(), $choiceValues)) {
                    $em->remove($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
        }
    }

    /**
     * @param \SplObjectStorage<ProductVariant, null> $changedProductVariants
     */
    public function updateProductVariants(EntityManagerInterface $em, UnitOfWork $uow, \SplObjectStorage $changedProductVariants): void
    {
        foreach ($changedProductVariants as $entity) {
            $choice = new ProductVariantChoice($entity->getOptionValues()->toArray());
            $this->logger->info('Updated product variant label.', [
                'old' => $entity->getLabel(),
                'new' => $choice->label,
            ]);

            $label = new \ReflectionProperty($entity, 'label');
            $label->setValue($entity, $choice->label);

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
        }
    }
}

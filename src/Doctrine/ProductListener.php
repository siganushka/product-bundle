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

        $changedProducts = $changedProductOptionValues = [];
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Product) {
                $changedProducts[] = $entity;
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ProductOptionValue && \array_key_exists('text', $uow->getEntityChangeSet($entity))) {
                $changedProductOptionValues[] = $entity;
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();
            $mappig = $collection->getMapping();
            if ($owner instanceof Product && is_subclass_of($mappig->targetEntity, ProductOption::class)) {
                $changedProducts[] = $owner;
            } elseif ($owner instanceof ProductOption && is_subclass_of($mappig->targetEntity, ProductOptionValue::class) && $product = $owner->getProduct()) {
                $changedProducts[] = $product;
            }
        }

        $this->generateVariants($em, $uow, $changedProducts);
        $this->updateVariantLabel($em, $uow, $changedProductOptionValues);
    }

    /**
     * @param array<int, Product> $products
     */
    public function generateVariants(EntityManagerInterface $em, UnitOfWork $uow, array $products): void
    {
        $generated = [];
        foreach ($products as $entity) {
            if (\in_array($entity, $generated, true)) {
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

            $choiceValues = array_map(fn (ProductVariantChoice $item) => $item->value, $choices);
            foreach ($entity->getVariants() as $variant) {
                if (!\in_array($variant->getValue(), $choiceValues)) {
                    $em->remove($variant);
                }
            }

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
            $generated[] = $entity;
        }
    }

    /**
     * @param array<int, ProductOptionValue> $productOptionValues
     */
    public function updateVariantLabel(EntityManagerInterface $em, UnitOfWork $uow, array $productOptionValues): void
    {
        $updated = [];
        foreach ($productOptionValues as $entity) {
            foreach ($entity->getVariants() as $variant) {
                if (\in_array($variant, $updated, true)) {
                    continue;
                }

                $choice = new ProductVariantChoice($variant->getOptionValues()->toArray());
                $this->logger->info('Updated product variant label.', [
                    'old' => $variant->getLabel(),
                    'new' => $choice->label,
                ]);

                $label = new \ReflectionProperty($variant, 'label');
                $label->setValue($variant, $choice->label);

                $uow->computeChangeSet($em->getClassMetadata($variant::class), $variant);
                $updated[] = $variant;
            }
        }
    }
}

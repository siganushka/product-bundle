<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine;

use BenTools\CartesianProduct\CartesianProduct;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

class ProductListener
{
    public function __construct(private readonly ProductVariantRepository $repository)
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

        $products = array_filter($changedProducts);
        foreach (array_unique($products, \SORT_REGULAR) as $entity) {
            foreach ($this->generateChoices($entity) as $choice) {
                $entity->addVariant($this->repository->createNew($choice)->setEnabled(false));
            }

            $uow->computeChangeSet($em->getClassMetadata($entity::class), $entity);
        }
    }

    /**
     * @return array<int, ProductVariantChoice>
     */
    private function generateChoices(Product $entity): array
    {
        $options = $entity->getOptions();
        if ($options->isEmpty()) {
            return [new ProductVariantChoice()];
        }

        $set = [];
        foreach ($options as $option) {
            $values = $option->getValues();
            if ($values->count()) {
                $set[] = $values;
            }
        }

        $cartesianProduct = new CartesianProduct($set);
        $asArray = $cartesianProduct->asArray();

        return array_map(fn (array $combinedOptionValues) => new ProductVariantChoice($combinedOptionValues), $asArray);
    }
}

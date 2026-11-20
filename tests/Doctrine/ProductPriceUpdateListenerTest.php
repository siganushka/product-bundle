<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\OneToManyAssociationMapping;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Doctrine\ProductPriceUpdateListener;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Tests\Fixtures\TestProduct;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOption;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductOptionValue;
use Siganushka\ProductBundle\Tests\Fixtures\TestProductVariant;

class ProductPriceUpdateListenerTest extends TestCase
{
    public function testRecalculatePricesFromPendingVariants(): void
    {
        $product = new TestProduct('T-Shirt');
        $lowest = $this->createVariant($product, 'lowest', 100, true);
        $priceless = $this->createVariant($product, 'priceless', null, true);
        $disabled = $this->createVariant($product, 'disabled', 5, false);
        $highest = $this->createVariant($product, 'highest', 300, true);

        $metadata = new ClassMetadata(TestProduct::class);
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityUpdates')->willReturn([$lowest, $priceless, $disabled, $highest]);
        // 同一商品下的多个变体只重算一次
        $uow->expects(static::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $product)
        ;

        (new ProductPriceUpdateListener())->onFlush(new OnFlushEventArgs($this->createEntityManager($uow, $metadata)));

        static::assertSame(100, $product->getLowestPrice());
        static::assertSame(300, $product->getHighestPrice());
    }

    public function testIgnoreVariantsScheduledForDeletion(): void
    {
        $product = new TestProduct('T-Shirt');
        $kept = $this->createVariant($product, 'kept', 100, true);
        $removed = $this->createVariant($product, 'removed', 500, true);

        $metadata = new ClassMetadata(TestProduct::class);
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityDeletions')->willReturn([$removed]);
        $uow->method('isScheduledForDelete')
            ->willReturnCallback(static fn (object $entity) => $removed === $entity)
        ;

        (new ProductPriceUpdateListener())->onFlush(new OnFlushEventArgs($this->createEntityManager($uow, $metadata)));

        static::assertSame(100, $product->getLowestPrice());
        static::assertSame(100, $product->getHighestPrice());
    }

    public function testRecalculatePricesFromPendingCollectionUpdates(): void
    {
        $product = new TestProduct('T-Shirt');
        $this->createVariant($product, 'lowest', 100, true);
        $this->createVariant($product, 'highest', 300, true);

        $metadata = new ClassMetadata(TestProduct::class);
        $uow = $this->createMock(UnitOfWork::class);
        // 变体的 owning side 已被清空，只能通过集合的 owner 定位商品
        $uow->method('getScheduledCollectionUpdates')->willReturn([
            $this->createCollectionWithOwner($product),
        ]);
        $uow->expects(static::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $product)
        ;

        (new ProductPriceUpdateListener())->onFlush(new OnFlushEventArgs($this->createEntityManager($uow, $metadata)));

        static::assertSame(100, $product->getLowestPrice());
        static::assertSame(300, $product->getHighestPrice());
    }

    public function testResetPricesWhenNoEnabledVariantHasPrice(): void
    {
        $product = new TestProduct('T-Shirt');
        $priceless = $this->createVariant($product, 'priceless', null, true);
        $this->createVariant($product, 'disabled', 100, false);

        $product->setLowestPrice(1);
        $product->setHighestPrice(2);

        $metadata = new ClassMetadata(TestProduct::class);
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityUpdates')->willReturn([$priceless]);
        $uow->expects(static::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $product)
        ;

        (new ProductPriceUpdateListener())->onFlush(new OnFlushEventArgs($this->createEntityManager($uow, $metadata)));

        static::assertNull($product->getLowestPrice());
        static::assertNull($product->getHighestPrice());
    }

    public function testSkipUnmanagedProduct(): void
    {
        $product = new TestProduct('T-Shirt');
        $variant = $this->createVariant($product, 'default', 100, true);

        $metadata = new ClassMetadata(TestProduct::class);
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityUpdates')->willReturn([$variant]);
        $uow->expects(static::never())->method('recomputeSingleEntityChangeSet');

        $em = $this->createEntityManager($uow, $metadata, contains: false);

        (new ProductPriceUpdateListener())->onFlush(new OnFlushEventArgs($em));

        static::assertNull($product->getLowestPrice());
        static::assertNull($product->getHighestPrice());
    }

    public function testIgnoreEntitiesWithoutProduct(): void
    {
        $metadata = new ClassMetadata(TestProduct::class);
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityInsertions')->willReturn([
            new TestProductOption('Color'),
            new TestProductVariant(),
        ]);
        $uow->method('getScheduledCollectionUpdates')->willReturn([
            $this->createCollectionWithOwner(new TestProductOption('Size')),
        ]);
        $uow->expects(static::never())->method('recomputeSingleEntityChangeSet');

        (new ProductPriceUpdateListener())->onFlush(new OnFlushEventArgs($this->createEntityManager($uow, $metadata)));
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function createEntityManager(UnitOfWork $uow, ClassMetadata $metadata, bool $contains = true): EntityManagerInterface
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getUnitOfWork')->willReturn($uow);
        $em->method('getClassMetadata')->willReturn($metadata);
        $em->method('contains')->willReturn($contains);

        return $em;
    }

    private function createVariant(TestProduct $product, string $code, ?int $price, bool $enabled): TestProductVariant
    {
        $variant = new TestProductVariant(ProductVariantChoice::create(new TestProductOptionValue($code, $code)));
        $variant->setPrice($price);
        $variant->setEnabled($enabled);

        $product->addVariant($variant);

        return $variant;
    }

    /**
     * @return PersistentCollection<int, object>
     */
    private function createCollectionWithOwner(object $owner): PersistentCollection
    {
        $mapping = OneToManyAssociationMapping::fromMappingArray([
            'fieldName' => 'variants',
            'sourceEntity' => $owner::class,
            'targetEntity' => TestProductVariant::class,
            'mappedBy' => 'product',
            'isOwningSide' => false,
        ]);

        $collection = new PersistentCollection(null, null, new ArrayCollection());
        $collection->setOwner($owner, $mapping);

        return $collection;
    }
}

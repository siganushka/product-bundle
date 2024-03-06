<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

/**
 * @extends ServiceEntityRepository<ProductOptionValue>
 *
 * @method ProductOptionValue      createNew(...$args)
 * @method ProductOptionValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductOptionValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductOptionValue[]    findAll()
 * @method ProductOptionValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductOptionValueRepository extends GenericEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOptionValue::class);
    }
}

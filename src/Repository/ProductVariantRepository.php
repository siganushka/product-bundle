<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\ProductVariant;

/**
 * @extends ServiceEntityRepository<Test>
 *
 * @method Test|null find($id, $lockMode = null, $lockVersion = null)
 * @method Test|null findOneBy(array $criteria, array $orderBy = null)
 * @method Test[]    findAll()
 * @method Test[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVariantRepository extends GenericEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariant::class);
    }
}

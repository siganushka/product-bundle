<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\ProductBundle\Entity\Product;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product      createNew(...$args)
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends GenericEntityRepository
{
}

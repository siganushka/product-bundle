<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Controller\Crud\DeleteItemTrait;
use Siganushka\GenericBundle\Controller\Crud\GetCollectionTrait;
use Siganushka\GenericBundle\Controller\Crud\GetItemTrait;
use Siganushka\GenericBundle\Controller\Crud\PostCollectionTrait;
use Siganushka\GenericBundle\Controller\Crud\PutItemTrait;
use Siganushka\ProductBundle\Dto\ProductQueryDto;
use Siganushka\ProductBundle\Form\ProductType;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    use GetCollectionTrait;
    use PostCollectionTrait;
    use GetItemTrait;
    use PutItemTrait;
    use DeleteItemTrait;

    public function __construct(private readonly ProductRepository $productRepository)
    {
        $this->configureCrud(
            entityName: $productRepository->getClassName(),
            entityForm: ProductType::class,
            queryDtoClass: ProductQueryDto::class,
        );
    }

    protected function createEntityQueryBuilder(ProductQueryDto $dto): QueryBuilder
    {
        return $this->productRepository->createQueryBuilderFromDto('p', $dto);
    }
}

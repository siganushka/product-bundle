<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Siganushka\GenericBundle\Controller\Crud\GetItemTrait;
use Siganushka\GenericBundle\Controller\Crud\PutItemTrait;
use Siganushka\ProductBundle\Form\ProductOptionType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product-options')]
class ProductOptionController extends AbstractController
{
    use GetItemTrait;
    use PutItemTrait;

    public function __construct(ProductOptionRepository $repository)
    {
        $this->configureCrud(
            entityName: $repository->getClassName(),
            entityForm: ProductOptionType::class,
        );
    }
}

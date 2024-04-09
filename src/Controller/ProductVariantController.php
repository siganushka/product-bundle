<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductVariantController extends AbstractFOSRestController
{
    private ProductRepository $productRepository;
    private ProductVariantRepository $variantRepository;

    public function __construct(ProductRepository $productRepository, ProductVariantRepository $variantRepository)
    {
        $this->productRepository = $productRepository;
        $this->variantRepository = $variantRepository;
    }

    /**
     * @Route("/product-variants/{id<\d+>}", methods={"GET"})
     */
    public function getItem(int $id): Response
    {
        $entity = $this->variantRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $id));
        }

        return $this->viewResponse($entity);
    }

    /**
     * @Route("/product-variants/{id<\d+>}", methods={"PUT", "PATCH"})
     */
    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->variantRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $id));
        }

        $form = $this->createForm(ProductVariantType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->viewResponse($form);
        }

        $entityManager->flush();

        return $this->viewResponse($entity);
    }

    /**
     * @Route("/product-variants/{id<\d+>}", methods={"DELETE"})
     */
    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->variantRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $id));
        }

        $entityManager->remove($entity);
        $entityManager->flush();

        return $this->viewResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function viewResponse($data = null, int $statusCode = null, array $headers = []): Response
    {
        $attributes = [
            'id',
            'price',
            'inventory',
            'img',
            'choice' => ['value', 'label'],
        ];

        $context = new Context();
        $context->setAttribute('attributes', $attributes);

        $view = $this->view($data, $statusCode, $headers);
        $view->setContext($context);

        return $this->handleView($view);
    }
}

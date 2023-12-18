<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Siganushka\ProductBundle\Form\Type\ProductVariantType;
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
     * @Route("/products/{productId<\d+>}/variants", methods={"GET"})
     */
    public function getCollection(int $productId): Response
    {
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $productId));
        }

        return $this->viewResponse($product->getVariants());
    }

    /**
     * @Route("/products/{productId<\d+>}/variants", methods={"POST"})
     */
    public function postCollection(Request $request, EntityManagerInterface $entityManager, int $productId): Response
    {
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $productId));
        }

        $entity = $this->variantRepository->createNew();
        $entity->setProduct($product);

        $form = $this->createForm(ProductVariantType::class, $entity);
        $form->submit($request->request->all());
        // dd($form['optionValues']->createView());

        if (!$form->isValid()) {
            return $this->viewResponse($form);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->viewResponse($entity);
    }

    /**
     * @Route("/variants/{id<\d+>}", methods={"GET"})
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
     * @Route("/variants/{id<\d+>}", methods={"PUT", "PATCH"})
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
     * @Route("/variants/{id<\d+>}", methods={"DELETE"})
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
            'name',
        ];

        $context = new Context();
        $context->setAttribute('attributes', $attributes);

        $view = $this->view($data, $statusCode, $headers);
        $view->setContext($context);

        return $this->handleView($view);
    }
}

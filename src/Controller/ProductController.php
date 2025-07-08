<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Form\ProductType;
use Siganushka\ProductBundle\Form\ProductVariantCollectionType;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    #[Route('/products', methods: 'GET')]
    public function getCollection(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->productRepository->createQueryBuilder('p')
            // ->where('p.variants IS NOT EMPTY')
        ;

        $page = $request->query->getInt('page', 1);
        $size = $request->query->getInt('size', 10);

        $pagination = $paginator->paginate($queryBuilder, $page, $size);

        return $this->createResponse($pagination);
    }

    #[Route('/products', methods: 'POST')]
    public function postCollection(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entity = $this->productRepository->createNew();

        $form = $this->createForm(ProductType::class, $entity);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->createResponse($entity, Response::HTTP_CREATED);
    }

    #[Route('/products/{id<\d+>}', methods: 'GET')]
    public function getItem(int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        return $this->createResponse($entity);
    }

    #[Route('/products/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $form = $this->createForm(ProductType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->createResponse($entity);
    }

    #[Route('/products/{id<\d+>}/variants', methods: ['PUT', 'PATCH'])]
    public function putItemVariants(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $form = $this->createForm(ProductVariantCollectionType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->createResponse($entity);
    }

    #[Route('/products/{id<\d+>}', methods: 'DELETE')]
    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $entityManager->remove($entity);
        $entityManager->flush();

        // 204 No Content
        return $this->createResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param PaginationInterface<int, mixed>|Product|null $data
     */
    protected function createResponse(PaginationInterface|Product|null $data, int $statusCode = Response::HTTP_OK, array $headers = []): Response
    {
        $attributes = [
            'id', 'name', 'img', 'updatedAt', 'createdAt',
            'options' => [
                'id', 'name',
                'values' => ['id', 'code', 'img', 'text'],
            ],
            'variants' => [
                'id', 'price', 'inventory', 'img', 'choiceValue', 'choiceLabel', 'outOfStock',
            ],
            'choices' => ['value', 'label'],
        ];

        return $this->json($data, $statusCode, $headers, compact('attributes'));
    }
}

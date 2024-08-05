<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Siganushka\GenericBundle\Exception\FormErrorException;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Form\ProductType;
use Siganushka\ProductBundle\Form\ProductVariantCollectionType;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[Route('/products')]
class ProductController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    #[Route(methods: 'GET')]
    public function getCollection(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->productRepository->createQueryBuilder('p')
            // filter empty variants
            // ->where('p.variants IS NOT EMPTY')
        ;

        if (null !== $name = $request->query->get('name')) {
            $queryBuilder->andWhere('p.name LIKE :name')
                ->setParameter('name', "%{$name}%");
        }

        $page = $request->query->getInt('page', 1);
        $size = $request->query->getInt('size', 10);

        $pagination = $paginator->paginate($queryBuilder, $page, $size);

        return $this->createResponse($pagination);
    }

    #[Route(methods: 'POST')]
    public function postCollection(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entity = $this->productRepository->createNew();

        $form = $this->createForm(ProductType::class, $entity);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new FormErrorException($form);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->createResponse($entity, Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', methods: 'GET')]
    public function getItem(int $id): Response
    {
        $entity = $this->productRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        return $this->createResponse($entity);
    }

    #[Route('/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        $form = $this->createForm(ProductType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            throw new FormErrorException($form);
        }

        try {
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $th) {
            throw new BadRequestHttpException('The associated data can be deleted if it is not empty.');
        }

        return $this->createResponse($entity);
    }

    #[Route('/{id<\d+>}/variants', methods: ['PUT', 'PATCH'])]
    public function putItemVariants(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        $form = $this->createForm(ProductVariantCollectionType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            throw new FormErrorException($form);
        }

        try {
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $th) {
            throw new BadRequestHttpException('The associated data can be deleted if it is not empty.');
        }

        return $this->createResponse($entity);
    }

    #[Route('/{id<\d+>}', methods: 'DELETE')]
    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        $entityManager->remove($entity);
        $entityManager->flush();

        return $this->createResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function createResponse(PaginationInterface|Product|null $data, int $statusCode = Response::HTTP_OK, array $headers = []): Response
    {
        $attributes = [
            'id', 'name', 'img', 'updatedAt', 'createdAt',
            'options' => [
                'id', 'name',
                'values' => ['id', 'img', 'text', 'note'],
            ],
            'variants' => [
                'id', 'price', 'inventory', 'img', 'choiceValue', 'choiceLabel', 'outOfStock',
            ],
            'choices' => ['value', 'label'],
        ];

        return $this->json($data, $statusCode, $headers, compact('attributes'));
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Siganushka\ProductBundle\Dto\ProductQueryDto;
use Siganushka\ProductBundle\Form\ProductType;
use Siganushka\ProductBundle\Form\ProductVariantCollectionType;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ProductController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    #[Route('/products', methods: 'GET')]
    public function getCollection(PaginatorInterface $paginator, #[MapQueryString] ProductQueryDto $dto): Response
    {
        $queryBuilder = $this->productRepository->createQueryBuilderByDto('p', $dto);
        $pagination = $paginator->paginate($queryBuilder, $dto->page, $dto->size);

        return $this->json($pagination, context: [
            AbstractNormalizer::GROUPS => ['collection'],
        ]);
    }

    #[Route('/products', methods: 'POST')]
    public function postCollection(Request $request, EntityManagerInterface $entityManager): Response
    {
        // @see https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-bool
        $combinable = $request->query->getBoolean('combinable', false);

        $entity = $this->productRepository->createNew();

        $form = $this->createForm(ProductType::class, $entity, compact('combinable'));
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->json($entity, Response::HTTP_CREATED, context: [
            AbstractNormalizer::GROUPS => ['item'],
        ]);
    }

    #[Route('/products/{id<\d+>}', methods: 'GET')]
    public function getItem(int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        return $this->json($entity, context: [
            AbstractNormalizer::GROUPS => ['item'],
        ]);
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

        return $this->json($entity, context: [
            AbstractNormalizer::GROUPS => ['item'],
        ]);
    }

    #[Route('/products/{id<\d+>}', methods: 'DELETE')]
    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $entityManager->remove($entity);
        $entityManager->flush();

        // 204 No Content
        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/products/{id<\d+>}/variants', methods: 'GET')]
    public function getVariants(int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        return $this->json($entity->getVariants(), context: [
            AbstractNormalizer::GROUPS => ['collection'],
        ]);
    }

    #[Route('/products/{id<\d+>}/variants', methods: ['PUT', 'PATCH'])]
    public function putVariants(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $form = $this->createForm(ProductVariantCollectionType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json($entity->getVariants(), context: [
            AbstractNormalizer::GROUPS => ['collection'],
        ]);
    }
}

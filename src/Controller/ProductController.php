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

class ProductController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    public function getCollection(PaginatorInterface $paginator, #[MapQueryString] ProductQueryDto $dto): Response
    {
        $queryBuilder = $this->productRepository->createQueryBuilderByDto('p', $dto);
        $pagination = $paginator->paginate($queryBuilder);

        return $this->json($pagination, context: [
            'groups' => ['product:collection'],
        ]);
    }

    public function postCollection(Request $request, EntityManagerInterface $entityManager): Response
    {
        // @see https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-bool
        $combinable = $request->query->getBoolean('combinable', false);

        $entity = $this->productRepository->createNew();

        $form = $this->createForm(ProductType::class, $entity, compact('combinable'));
        $form->submit($request->getPayload()->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->json($entity, Response::HTTP_CREATED, context: [
            'groups' => ['product:item'],
        ]);
    }

    public function getItem(int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        return $this->json($entity, context: [
            'groups' => ['product:item'],
        ]);
    }

    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $form = $this->createForm(ProductType::class, $entity);
        $form->submit($request->getPayload()->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json($entity, context: [
            'groups' => ['product:item'],
        ]);
    }

    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $entityManager->remove($entity);
        $entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    public function getVariants(int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        return $this->json($entity->getVariants(), context: [
            'groups' => ['product_variant:collection'],
        ]);
    }

    public function putVariants(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productRepository->find($id)
            ?? throw $this->createNotFoundException();

        $form = $this->createForm(ProductVariantCollectionType::class, $entity);
        $form->submit($request->getPayload()->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json($entity->getVariants(), context: [
            'groups' => ['product_variant:collection'],
        ]);
    }
}

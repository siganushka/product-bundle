<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Siganushka\GenericBundle\Exception\FormErrorException;
use Siganushka\ProductBundle\Entity\ProductVariant;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ProductVariantController extends AbstractController
{
    public function __construct(private readonly ProductVariantRepository $variantRepository)
    {
    }

    #[Route('/product-variants/{id<\d+>}', methods: 'GET')]
    public function getItem(int $id): Response
    {
        $entity = $this->variantRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        return $this->createResponse($entity);
    }

    #[Route('/product-variants/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->variantRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        $form = $this->createForm(ProductVariantType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            throw new FormErrorException($form);
        }

        $entityManager->flush();

        return $this->createResponse($entity);
    }

    #[Route('/product-variants/{id<\d+>}', methods: 'DELETE')]
    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->variantRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(\sprintf('Resource #%d not found.', $id));
        }

        $entityManager->remove($entity);
        $entityManager->flush();

        // 204 No Content
        return $this->createResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function createResponse(?ProductVariant $data, int $statusCode = Response::HTTP_OK, array $headers = []): Response
    {
        $attributes = ['id', 'price', 'inventory', 'img', 'choiceValue', 'choiceLabel', 'outOfStock'];

        return $this->json($data, $statusCode, $headers, compact('attributes'));
    }
}

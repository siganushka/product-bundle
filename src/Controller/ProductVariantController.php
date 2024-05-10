<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Siganushka\GenericBundle\Exception\FormErrorException;
use Siganushka\ProductBundle\Form\ProductVariantType;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductVariantController extends AbstractController
{
    private SerializerInterface $serializer;
    private ProductVariantRepository $variantRepository;

    public function __construct(SerializerInterface $serializer, ProductVariantRepository $variantRepository)
    {
        $this->serializer = $serializer;
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

        return $this->createResponse($entity);
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
            throw new FormErrorException($form);
        }

        $entityManager->flush();

        return $this->createResponse($entity);
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

        return $this->createResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param mixed $data
     */
    protected function createResponse($data = null, int $statusCode = Response::HTTP_OK, array $headers = []): Response
    {
        $attributes = ['id', 'price', 'inventory', 'img', 'choiceValue', 'choiceLabel', 'outOfStock'];

        $json = $this->serializer->serialize($data, 'json', compact('attributes'));

        return JsonResponse::fromJsonString($json, $statusCode, $headers);
    }
}

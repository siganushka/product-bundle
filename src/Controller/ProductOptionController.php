<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Siganushka\ProductBundle\Form\ProductOptionType;
use Siganushka\ProductBundle\Repository\ProductOptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ProductOptionController extends AbstractController
{
    public function __construct(private readonly ProductOptionRepository $productOptionRepository)
    {
    }

    #[Route('/product-options/{id<\d+>}', methods: 'GET')]
    public function getItem(int $id): Response
    {
        $entity = $this->productOptionRepository->find($id)
            ?? throw $this->createNotFoundException();

        return $this->json($entity, context: [
            AbstractNormalizer::GROUPS => ['item'],
        ]);
    }

    #[Route('/product-options/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->productOptionRepository->find($id)
            ?? throw $this->createNotFoundException();

        $form = $this->createForm(ProductOptionType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json($entity, context: [
            AbstractNormalizer::GROUPS => ['item'],
        ]);
    }
}

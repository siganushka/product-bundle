<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Form\ProductOptionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductOptionController extends AbstractController
{
    public function getItem(ProductOption $entity): Response
    {
        return $this->json($entity, context: [
            'groups' => ['product_option.item'],
        ]);
    }

    public function putItem(Request $request, EntityManagerInterface $entityManager, ProductOption $entity): Response
    {
        $form = $this->createForm(ProductOptionType::class, $entity);
        $form->submit($request->getPayload()->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json($entity, context: [
            'groups' => ['product_option.item'],
        ]);
    }
}

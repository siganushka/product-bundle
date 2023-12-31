<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Knp\Component\Pager\PaginatorInterface;
use Siganushka\ProductBundle\Form\OptionType;
use Siganushka\ProductBundle\Repository\OptionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OptionController extends AbstractFOSRestController
{
    private OptionRepository $optionRepository;

    public function __construct(OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @Route("/options", methods={"GET"})
     */
    public function getCollection(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->optionRepository->createQueryBuilder('o');

        $page = $request->query->getInt('page', 1);
        $size = $request->query->getInt('size', 10);

        $pagination = $paginator->paginate($queryBuilder, $page, $size);

        return $this->viewResponse($pagination);
    }

    /**
     * @Route("/options", methods={"POST"})
     */
    public function postCollection(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Option */
        $entity = $this->optionRepository->createNew();

        $form = $this->createForm(OptionType::class, $entity);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->viewResponse($form);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->viewResponse($entity, Response::HTTP_CREATED);
    }

    /**
     * @Route("/options/{id<\d+>}", methods={"GET"})
     */
    public function getItem(int $id): Response
    {
        $entity = $this->optionRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $id));
        }

        return $this->viewResponse($entity);
    }

    /**
     * @Route("/options/{id<\d+>}", methods={"PUT", "PATCH"})
     */
    public function putItem(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->optionRepository->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Resource #%d not found.', $id));
        }

        $form = $this->createForm(OptionType::class, $entity);
        $form->submit($request->request->all(), !$request->isMethod('PATCH'));

        if (!$form->isValid()) {
            return $this->viewResponse($form);
        }

        $entityManager->flush();

        return $this->viewResponse($entity);
    }

    /**
     * @Route("/options/{id<\d+>}", methods={"DELETE"})
     */
    public function deleteItem(EntityManagerInterface $entityManager, int $id): Response
    {
        $entity = $this->optionRepository->find($id);
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
            'id', 'name', 'sort', 'updatedAt', 'createdAt',
            'values' => [
                'code',
                'text',
                'img',
                'sort',
                'updatedAt',
                'createdAt',
            ],
        ];

        $context = new Context();
        $context->setAttribute('attributes', $attributes);

        $view = $this->view($data, $statusCode, $headers);
        $view->setContext($context);

        return $this->handleView($view);
    }
}

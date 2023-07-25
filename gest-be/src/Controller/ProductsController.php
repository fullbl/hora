<?php

namespace App\Controller;

use App\Mapper\ProductMapper;
use App\Repository\LogRepository;
use App\Repository\ProductRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
class ProductsController extends AbstractController
{
    public function __construct(
        private ProductRepository $repo,
        private ProductMapper $mapper,
        private ValidatorInterface $validator,
        private LogRepository $logRepo
    ) {
    }

    #[Route('/products', methods: ['GET'], name: 'products_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], [
            'groups' => 'product'
        ]);
    }

    #[Route('/products', methods: ['POST'], name: 'product_create')]
    public function create(Request $request): JsonResponse
    {
        $product = $this->mapper->fromRequest($request);
        $errors = $this->validator->validate($product);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($product, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => 'product'
        ]);
    }

    #[Route('/products/{id}', methods: ['GET'], name: 'products_show')]
    public function show(int $id): JsonResponse
    {
        $product = $this->repo->find($id);
        if (null === $product) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => 'product'
        ]);
    }

    #[Route('/products/{id}', methods: ['PUT'], name: 'product_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $product = $this->repo->find($id);

        if (null === $product) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $product = $this->mapper->fill($product, $request);
        $errors = $this->validator->validate($product);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->logRepo->prepareForEntity($product);
            $this->repo->save($product, true);
            $this->logRepo->saveForEntity($product);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => 'product'
        ]);
    }

    #[Route('/products/{id}', methods: ['DELETE'], name: 'product_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $product = $this->repo->find($id);
        if (null === $product) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($product, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }
}

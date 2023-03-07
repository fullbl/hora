<?php

namespace App\Controller;

use App\Mapper\ProductMapper;
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
    public function __construct(private ProductRepository $repo, private ValidatorInterface $validator)
    {
    }

    #[Route('/products', methods: ['GET'], name: 'customers_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/products', methods: ['POST'], name: 'customer_create')]
    public function create(Request $request): JsonResponse
    {
        $product = ProductMapper::fromRequest($request);
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
        return $this->json($product);
    }

    #[Route('/products/{id}', methods: ['GET'], name: 'customers_show')]
    public function show(int $id): JsonResponse
    {
        $product = $this->repo->find($id);
        if (null === $product) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($product);
    }

    #[Route('/products/{id}', methods: ['PUT'], name: 'customer_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $product = $this->repo->find($id);
        if (null === $product) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $product = ProductMapper::fill($product, $request);
        $errors = $this->validator->validate($product);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
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
        return $this->json($product);
    }

    #[Route('/products/{id}', methods: ['DELETE'], name: 'customer_delete')]
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

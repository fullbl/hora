<?php

namespace App\Controller;

use App\Entity\Product;
use App\Mapper\ProductMapper;
use App\Repository\ProductRepository;
use Exception;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[WithMonologChannel('actions')]
class ProductsController extends AbstractController
{
    public function __construct(
        private ProductRepository $repo,
        private ProductMapper $mapper,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/water_boxes', methods: ['GET'], name: 'water_boxes_list')]
    public function waterBoxes(): JsonResponse
    {
        return $this->json($this->repo->findBy(
            ['type' => Product::TYPE_WATER_BOX],
            ['name' => 'ASC']
        ), Response::HTTP_OK, [], [
            'groups' => 'product-list'
        ]);
    }

    #[Route('/mixes', methods: ['GET'], name: 'mixes_list')]
    public function mixes(): JsonResponse
    {
        return $this->json($this->repo->findBy(
            ['type' => Product::TYPE_MIX],
            ['name' => 'ASC']
        ), Response::HTTP_OK, [], [
            'groups' => 'product-list'
        ]);
    }

    #[Route('/seeds', methods: ['GET'], name: 'seeds_list')]
    public function seeds(): JsonResponse
    {
        return $this->json($this->repo->findBy(
            ['type' => Product::TYPE_SEED],
            ['name' => 'ASC']
        ), Response::HTTP_OK, [], [
            'groups' => 'product-list'
        ]);
    }

    #[Route('/sellable', methods: ['GET'], name: 'sellable_list')]
    public function sellable(): JsonResponse
    {
        return $this->json(
            $this->repo->findBy(
                ['type' => [
                    Product::TYPE_SEED,
                    Product::TYPE_EXTRA,
                ]],
                ['name' => 'ASC']
            ),
            Response::HTTP_OK,
            [],
            [
                'groups' => 'product-list'
            ]
        );
    }

    #[Route('/products', methods: ['GET'], name: 'products_list')]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (null === $user) {
            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_FORBIDDEN,
            );
        }

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $products = $this->repo->findAll();
        } else {
            $products = $this->repo->findAllFiltered($user->getZones());
        }

        return $this->json($products, Response::HTTP_OK, [], [
            'groups' => 'product-list'
        ]);
    }

    #[Route('/products', methods: ['POST'], name: 'product_create')]
    public function create(Request $request): JsonResponse
    {
        $product = $this->mapper->fromRequest($request);
        $errors = $this->validator->validate($product, null, [$request->toArray()['type']]);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($product, true);
            $this->logger->notice('[PRODUCTS] Product created', [
                'product' => $product->getId(),
                'data' => $request->toArray(),
            ]);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => 'product-list'
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
            'groups' => 'product-list'
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
        $errors = $this->validator->validate($product, null, [$request->toArray()['type']]);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($product, true);
            $this->logger->notice('[PRODUCTS] Product updated', [
                'product' => $product->getId(),
                'data' => $request->toArray()
            ]);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => 'product-list'
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
            $this->logger->notice('[PRODUCTS] Product deleted', [
                'product' => $id,
                'data' => $request->toArray()
            ]);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }
}

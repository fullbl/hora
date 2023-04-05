<?php

namespace App\Controller;

use App\Mapper\StorageMapper;
use App\Repository\StorageRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_OPERATOR')]
class StoragesController extends AbstractController
{
    public function __construct(private StorageRepository $repo, private ValidatorInterface $validator)
    {
    }

    #[Route('/storages', methods: ['GET'], name: 'storages_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/storages', methods: ['POST'], name: 'storage_create')]
    public function create(Request $request): JsonResponse
    {
        $storage = StorageMapper::fromRequest($request);
        $errors = $this->validator->validate($storage);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($storage, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($storage);
    }

    #[Route('/storages/{id}', methods: ['GET'], name: 'storages_show')]
    public function show(int $id): JsonResponse
    {
        $storage = $this->repo->find($id);
        if (null === $storage) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($storage);
    }

    #[Route('/storages/{id}', methods: ['PUT'], name: 'storage_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $storage = $this->repo->find($id);
        if (null === $storage) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $storage = StorageMapper::fill($storage, $request);
        $errors = $this->validator->validate($storage);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($storage, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($storage);
    }

    #[Route('/storages/{id}', methods: ['DELETE'], name: 'storage_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $storage = $this->repo->find($id);
        if (null === $storage) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($storage, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }
}

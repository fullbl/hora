<?php

namespace App\Controller;

use App\Mapper\HorderMapper;
use App\Repository\HorderRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_OPERATOR')]
class HordersController extends AbstractController
{
    public function __construct(private HorderRepository $repo, private ValidatorInterface $validator)
    {
    }

    #[Route('/horders', methods: ['GET'], name: 'horders_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/horders', methods: ['POST'], name: 'horder_create')]
    public function create(Request $request): JsonResponse
    {
        $horder = HorderMapper::fromRequest($request);
        $errors = $this->validator->validate($horder);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($horder, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($horder);
    }

    #[Route('/horders/{id}', methods: ['GET'], name: 'horders_show')]
    public function show(int $id): JsonResponse
    {
        $horder = $this->repo->find($id);
        if (null === $horder) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $horder;
    }

    #[Route('/horders/{id}', methods: ['PUT'], name: 'horder_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $horder = $this->repo->find($id);
        if (null === $horder) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $horder = HorderMapper::fill($horder, $request);
        $errors = $this->validator->validate($horder);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($horder, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($horder);
    }

    #[Route('/horders/{id}', methods: ['DELETE'], name: 'horder_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $horder = $this->repo->find($id);
        if (null === $horder) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($horder, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json($horder);
    }
}

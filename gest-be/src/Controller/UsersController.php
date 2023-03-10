<?php

namespace App\Controller;

use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
class UsersController extends AbstractController
{
    public function __construct(private UserRepository $repo, private ValidatorInterface $validator)
    {
    }

    #[Route('/users', methods: ['GET'], name: 'users_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/users', methods: ['POST'], name: 'user_create')]
    public function create(Request $request): JsonResponse
    {
        $user = UserMapper::fromRequest($request);
        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($user, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($user);
    }

    #[Route('/users/{id}', methods: ['GET'], name: 'users_show')]
    public function show(int $id): JsonResponse
    {
        $user = $this->repo->find($id);
        if (null === $user) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($user);
    }

    #[Route('/users/{id}', methods: ['PUT'], name: 'user_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->repo->find($id);
        if (null === $user) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $user = UserMapper::fill($user, $request);
        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($user, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($user);
    }

    #[Route('/users/{id}', methods: ['DELETE'], name: 'user_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $user = $this->repo->find($id);
        if (null === $user) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($user, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }
}

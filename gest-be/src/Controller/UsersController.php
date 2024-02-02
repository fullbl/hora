<?php

namespace App\Controller;

use App\Entity\User;
use App\Mapper\UserMapper;
use App\Repository\LogRepository;
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
    public function __construct(
        private UserRepository $repo,
        private UserMapper $mapper,
        private ValidatorInterface $validator,
        private LogRepository $logRepo,
    ) {
    }

    #[Route('users', methods: ['GET'], name: 'users_list')]
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

        if($this->isGranted('ROLE_SUPER_ADMIN')){
            $users = $this->repo->findAll();
        }
        else{
            $users = $this->repo->findAllFiltered($user->getZones());
        }

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'user-list']);
    }

    #[Route('users', methods: ['POST'], name: 'user_create')]
    public function create(Request $request): JsonResponse
    {
        $user = $this->mapper->fromRequest($request);
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
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user-list']);
    }

    #[Route('users/{id}', methods: ['GET'], name: 'users_show')]
    public function show(int $id): JsonResponse
    {
        $user = $this->repo->find($id);
        if (null === $user) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($user);
    }

    #[Route('users/{id}', methods: ['PUT'], name: 'user_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->repo->find($id);
        if (null === $user) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $user = $this->mapper->fill($user, $request);
        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->logRepo->prepareForEntity($user);
            $this->repo->save($user, true);
            $this->logRepo->saveForEntity($user);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user-list']);
    }

    #[Route('users/{id}', methods: ['DELETE'], name: 'user_delete')]
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

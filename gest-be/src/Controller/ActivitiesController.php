<?php

namespace App\Controller;

use App\Mapper\ActivityMapper;
use App\Repository\ActivityRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_OPERATOR')]
class ActivitiesController extends AbstractController
{
    public function __construct(
        private ActivityRepository $repo, 
        private ActivityMapper $mapper,
        private ValidatorInterface $validator)
    {
    }

    #[Route('/activities', methods: ['GET'], name: 'activities_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], ['groups' => 'activity-list']);
    }

    #[Route('/activities', methods: ['POST'], name: 'activity_create')]
    public function create(Request $request): JsonResponse
    {
        $activity = $this->mapper->fromRequest($request);
        $errors = $this->validator->validate($activity);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($activity, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json('');
    }

    #[Route('/activities/{id}', methods: ['GET'], name: 'activities_show')]
    public function show(int $id): JsonResponse
    {
        $activity = $this->repo->find($id);
        if (null === $activity) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($activity);
    }

    #[Route('/activities/{id}', methods: ['PUT'], name: 'activity_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $activity = $this->repo->find($id);
        if (null === $activity) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $activity = $this->mapper->fill($activity, $request);
        $errors = $this->validator->validate($activity);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($activity, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($activity);
    }

    #[Route('/activities/{id}', methods: ['DELETE'], name: 'activity_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $activity = $this->repo->find($id);
        if (null === $activity) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($activity, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }
}

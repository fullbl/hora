<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Mapper\ActivityMapper;
use App\Repository\ActivityRepository;
use App\Service\HAService;
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
        private ValidatorInterface $validator
    ) {
    }
    
    #[Route('/ha/soaking', methods: ['POST'], name: 'activities_soaking_trigger')]
    public function soakingTrigger(Request $request): JsonResponse
    {
        $ids = $request->toArray()['ids'] ?? [];
        foreach($ids as $id){
            $activity = $this->repo->find($id);
            $activity->setStatus(Activity::STATUS_DONE);
            $this->repo->save($activity, true);
        }

        return $this->json(['status' => 'ok']);
    }

    #[Route('/soaking', methods: ['POST'], name: 'activities_soaking')]
    public function soaking(Request $request, HAService $hAService): JsonResponse
    {
        $data = $request->toArray();
        $soakingTime = new \DateTime($data['time']);
        $activities = [];
        foreach ($data['deliveries'] as $delivery) {
            $activity = $this->mapper->fromArray([
                'time' => $data['time'],
                'delivery' => $delivery,
                'step' => $data['step'],
                'year' => $data['year'],
                'week' => $data['week'],
                'qty' => $data['qty'],
                'status' => Activity::STATUS_PLANNED,
            ]);
            $this->repo->save($activity, true);
            $activities[] = $activity->getId();
        }
        if (!$hAService->enqueueScript(
            $soakingTime,
            $activities,
            'script.pompa_' . $data['box'],
            sprintf('Pompa %s %s', $data['box'], $soakingTime->format('Y-m-d H:i'))
        )) {
            return $this->json(['status' => 'error'], 400);
        }


        if (isset($activity)) {
            $this->repo->save($activity, true);
        }

        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], ['groups' => 'activity-list']);
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

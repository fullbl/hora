<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Mapper\ActivityMapper;
use App\Repository\ActivityRepository;
use App\Service\HAService;
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

#[IsGranted('ROLE_OPERATOR')]
#[WithMonologChannel('actions')]
class ActivitiesController extends AbstractController
{
    public function __construct(
        private ActivityRepository $repo,
        private ActivityMapper $mapper,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/ha/soaking', methods: ['POST'], name: 'activities_soaking_trigger')]
    public function soakingTrigger(Request $request): JsonResponse
    {
        $ids = $request->toArray()['ids'] ?? [];
        foreach ($ids as $id) {
            $activity = $this->repo->find($id);
            $activity->setStatus(Activity::STATUS_DONE);
            $this->repo->save($activity, true);
        }
        $this->logger->notice('[ACTIVITIES] Soaking done', ['ids' => $ids]);

        return $this->json(['status' => 'ok']);
    }

    #[Route('/soaking', methods: ['POST'], name: 'activities_soaking')]
    public function soaking(Request $request, HAService $hAService): JsonResponse
    {
        $data = $request->toArray();
        $soakingTime = new \DateTime($data['time']);
        $activities = [];

        foreach ($data['soakings'] as $soaking) {
            foreach ($soaking['deliveries'] as $delivery) {
                $activity = $this->mapper->fromArray([
                    'time' => $data['time'],
                    'delivery' => $delivery,
                    'step' => $soaking['step'],
                    'year' => $data['year'],
                    'week' => $data['week'],
                    'qty' => $soaking['qty'],
                    'status' => Activity::STATUS_PLANNED,

                ]);
                $this->repo->save($activity, true);
                $activities[] = $activity;

                $this->logger->notice('[ACTIVITIES] Soaking planned', [
                    'activity' => $activity->getId(),
                    'data' => $data
                ]);
            }
        }

        $scriptId = $hAService->enqueueScript(
            $soakingTime,
            $activities,
            'script.pompa_' . $data['box'],
            sprintf('Pompa %s %s', $data['box'], $soakingTime->format('Y-m-d H:i'))
        );

        if (null === $scriptId) {
            $this->logger->error('[ACTIVITIES] Soaking script failed', [
                'soakingTime' => $soakingTime,
                'box' => $data['box'],
                'activities' => $activities,
            ]);
            return $this->json(['status' => 'error'], 400);
        }

        foreach ($activities as $activity) {
            $activity->setData([
                'script' => $scriptId,
                'box' => $data['box'],
            ]);
            $this->repo->save($activity, true);

            $this->logger->notice('[ACTIVITIES] Soaking enqueued', [
                'activity' => $activity,
                'scriptId' => $scriptId,
                'box' => $data['box'],
            ]);
        }

        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], ['groups' => 'activity-list']);
    }

    #[Route('/planting', methods: ['POST'], name: 'activities_planting')]
    public function planting(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $activities = [];

        foreach ($data['plantings'] as $planting) {
            foreach ($planting['deliveries'] as $delivery) {
                $activityData = [
                    'delivery' => $delivery,
                    'step' => $planting['step'],
                    'year' => $data['year'],
                    'week' => $data['week'],
                    'qty' => $planting['qty'],
                    'status' => Activity::STATUS_PLANNED,

                ];
                $activity = $this->mapper->fromArray();
                $this->repo->save($activity, true);
                $activities[] = $activity;

                $this->logger->notice('[ACTIVITIES] Planting planned', [
                    'activity' => $activity,
                    'data' => $activityData
                ]);
            }
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
            $this->logger->notice('[ACTIVITIES] Activity created', [
                'activity' => $activity->getId(),
                'data' => $request->toArray(),
            ]);
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
        $baseActivity = $this->repo->find($id);
        if (null === $baseActivity) {
            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $activity = $this->mapper->fill($baseActivity, $request);
        $errors = $this->validator->validate($activity);

        if ($errors->count() > 0) {
            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($activity, true);
            $this->logger->notice('[ACTIVITIES] Activity updated', [
                'activity' => $activity->getId(),
                'data' => $request->toArray()
            ]);
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
            $this->logger->notice('[ACTIVITIES] Activity deleted', [
                'activity' => $id,
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

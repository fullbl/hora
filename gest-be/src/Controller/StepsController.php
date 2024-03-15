<?php

namespace App\Controller;

use App\Mapper\StepMapper;
use App\Repository\StepRepository;
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
class StepsController extends AbstractController
{
    public function __construct(
        private StepRepository $repo, 
        private StepMapper $mapper,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ){
    }

    #[Route('/steps', methods: ['GET'], name: 'steps_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/steps', methods: ['POST'], name: 'step_create')]
    public function create(Request $request): JsonResponse
    {
        $step = $this->mapper->fromRequest($request);
        $errors = $this->validator->validate($step);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($step, true);
            $this->logger->notice('[STEPS] Step created', [
                'step' => $step->getId(),
                'data' => $request->toArray()
            ]);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($step);
    }

    #[Route('/steps/{id}', methods: ['GET'], name: 'steps_show')]
    public function show(int $id): JsonResponse
    {
        $step = $this->repo->find($id);
        if (null === $step) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($step);
    }

    #[Route('/steps/{id}', methods: ['PUT'], name: 'step_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $step = $this->repo->find($id);
        if (null === $step) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $step = $this->mapper->fill($step, $request);
        $errors = $this->validator->validate($step);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($step, true);
            $this->logger->notice('[STEPS] Step updated', [
                'step' => $step->getId(),
                'data' => $request->toArray()
            ]);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        return $this->json($step);
    }

    #[Route('/steps/{id}', methods: ['DELETE'], name: 'step_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $step = $this->repo->find($id);
        if (null === $step) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($step, true);
            $this->logger->notice('[STEPS] Step deleted', [
                'step' => $id,
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

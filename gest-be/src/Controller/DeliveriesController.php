<?php

namespace App\Controller;

use App\Mapper\DeliveryMapper;
use App\Repository\DeliveryRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
class DeliveriesController extends AbstractController
{
    public function __construct(
        private DeliveryRepository $repo, 
        private DeliveryMapper $mapper,
        private ValidatorInterface $validator)
    {
    }

    #[Route('/deliveries', methods: ['GET'], name: 'deliveries_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], ['groups' => 'delivery-list']);
    }

    #[Route('/deliveries', methods: ['POST'], name: 'delivery_create')]
    public function create(Request $request): JsonResponse
    {
        $delivery = $this->mapper->fromRequest($request);
        $errors = $this->validator->validate($delivery);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST,
            );
        }
        try {
            $this->repo->save($delivery, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }

    #[Route('/deliveries/{id}', methods: ['GET'], name: 'deliveries_show')]
    public function show(int $id): JsonResponse
    {
        $delivery = $this->repo->find($id);
        if (null === $delivery) {

            return $this->json('', Response::HTTP_NOT_FOUND);
        }

        return $this->json($delivery);
    }

    #[Route('/deliveries/{id}', methods: ['PUT'], name: 'delivery_update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $delivery = $this->repo->find($id);
        if (null === $delivery) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $delivery = $this->mapper->fill($delivery, $request);
        $errors = $this->validator->validate($delivery);
        if ($errors->count() > 0) {

            return $this->json(
                $errors,
                Response::HTTP_BAD_REQUEST
            );
        }
        try {
            $this->repo->save($delivery, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
        
        return $this->json('');
    }

    #[Route('/deliveries/{id}', methods: ['DELETE'], name: 'delivery_delete')]
    public function delete(int $id, Request $request): JsonResponse
    {
        $delivery = $this->repo->find($id);
        if (null === $delivery) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $this->repo->remove($delivery, true);
        } catch (Exception $e) {

            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $this->json('');
    }
}

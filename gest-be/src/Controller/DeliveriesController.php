<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\DeliveryProduct;
use App\Entity\Product;
use App\Mapper\DeliveryMapper;
use App\Mapper\DeliveryProductMapper;
use App\Repository\DeliveryRepository;
use App\Repository\LogRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private const DASHBOARD_WEEKS = 4;

    public function __construct(
        private DeliveryRepository $repo,
        private DeliveryMapper $mapper,
        private ValidatorInterface $validator,
        private LogRepository $logRepo,
    ) {
    }

    #[Route('/deliveries', methods: ['GET'], name: 'deliveries_list')]
    public function list(): JsonResponse
    {
        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], ['groups' => 'delivery-list']);
    }
    
    #[Route('/deliveries/{fromDate}', methods: ['GET'], name: 'deliveries_dashboard')]
    public function dashboard(\DateTimeImmutable $fromDate): JsonResponse
    {        
        return $this->json($this->repo->findNext($fromDate, self::DASHBOARD_WEEKS), Response::HTTP_OK, [], ['groups' => 'delivery-dash']);
    }

    #[Route('/deliveries', methods: ['POST'], name: 'delivery_create')]
    public function create(Request $request): JsonResponse
    {
        foreach ($this->mapper->fromRequest($request) as $delivery) {

            $errors = $this->validator->validate($delivery);
            if ($errors->count() > 0) {

                return $this->json(
                    $errors,
                    Response::HTTP_BAD_REQUEST,
                );
            }
            try {
                $this->repo->save($delivery);
            } catch (Exception $e) {

                return $this->json(
                    ['error' => $e->getMessage()],
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }
        }
        $this->repo->flush();
        return $this->json('');
    }

    #[Route('/deliveries/move', methods: ['PUT'], name: 'delivery_move')]
    public function move(Request $request, DeliveryProductMapper $mapper, EntityManagerInterface $em): JsonResponse
    {
        $moves = $request->toArray();
        $baseDelivery = $this->repo->find($moves['delivery']);
        if (null === $baseDelivery) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        foreach ($moves['deliveries'] as $move) {
            $delivery = $this->repo->find($move['delivery']);
            if (null === $delivery) {
                $delivery = clone $baseDelivery;
                $delivery->setCustomer(null);
                $em->persist($delivery);                
            }
            $delivery->setDeliveryProducts($mapper->map($move['deliveryProducts'], $delivery));
        }

        $this->repo->flush();

        return $this->json('');
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
            $this->logRepo->prepareForEntity($delivery);
            $this->repo->save($delivery, true);
            $this->logRepo->saveForEntity($delivery);
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

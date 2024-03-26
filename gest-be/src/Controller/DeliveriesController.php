<?php

namespace App\Controller;

use App\Entity\DeliveryProduct;
use App\Mapper\DeliveryMapper;
use App\Mapper\DeliveryProductMapper;
use App\Repository\DeliveryRepository;
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
class DeliveriesController extends AbstractController
{
    private const DASHBOARD_WEEKS = 4;

    public function __construct(
        private DeliveryRepository $repo,
        private DeliveryMapper $mapper,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/deliveries', methods: ['GET'], name: 'deliveries_list')]
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

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $deliveries = $this->repo->findAll();
        } else {
            $deliveries = $this->repo->findAllFiltered($user->getZones());
        }

        return $this->json($deliveries, Response::HTTP_OK, [], ['groups' => 'delivery-list']);
    }

    #[Route('/deliveries/{fromDate}', methods: ['GET'], name: 'deliveries_dashboard')]
    public function dashboard(\DateTimeImmutable $fromDate): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (null === $user) {
            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_FORBIDDEN,
            );
        }

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $deliveries = $this->repo->findNext($fromDate, self::DASHBOARD_WEEKS);
        } else {
            $deliveries = $this->repo->findNext($fromDate, self::DASHBOARD_WEEKS, $user->getZones());
        }

        return $this->json($deliveries, Response::HTTP_OK, [], ['groups' => 'delivery-dash']);
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
                $this->logger->notice('[DELIVERIES] Delivery created', [
                    'delivery' => $delivery->getId(),
                    'data' => $request->toArray(),
                ]);
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
    public function move(Request $request, DeliveryProductMapper $mapper): JsonResponse
    {
        $moves = $request->toArray();
        $baseDelivery = $this->repo->find($moves['delivery']);
        if (null === $baseDelivery) {

            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_NOT_FOUND,
            );
        }
        $this->logger->notice('[DELIVERIES] Move deliveries', [
            'data' => $moves,
        ]);

        foreach ($moves['deliveries'] as $move) {
            $delivery = $this->repo->find($move['delivery']);
            if (null === $delivery) {
                $productIds = array_map(fn ($dp) => $dp['product']['id'], $move['deliveryProducts']);
                $delivery = $this->repo->findFreeDeliveryInSameWeek($baseDelivery);
                foreach ($delivery->getDeliveryProducts() as $dp) {
                    if (in_array($dp->getProduct()?->getId(), $productIds)) {
                        $delivery->removeDeliveryProduct($dp);
                    }
                }

                foreach ($mapper->map($move['deliveryProducts'], $delivery) as $product) {
                    $delivery->addDeliveryProduct($product);
                }
            } else {
                $delivery->setDeliveryProducts($mapper->map($move['deliveryProducts'], $delivery));
            }

            $this->logger->notice('[DELIVERIES] Delivery moved', [
                'delivery' => $delivery->getId(),
                'products' => json_encode($delivery->getDeliveryProducts()->map(fn (DeliveryProduct $dp) => [
                    'id' => $dp->getProduct()->getId(),
                    'qty' => $dp->getQty()
                ])->toArray()),
            ]);
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
            $this->repo->save($delivery, true);

            $this->logger->notice('[DELIVERIES] Delivery updated', [
                'delivery' => $delivery->getId(),
                'data' => $request->toArray()
            ]);
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
            $delivery->setDeletedAt(new \DateTimeImmutable());
            $delivery->setDeletedReason($request->toArray()['reason'] ?? '');

            $freeDelivery = $this->repo->findFreeDeliveryInSameWeek($delivery);
            $deliveryProducts = $delivery->getDeliveryProducts();
            foreach ($deliveryProducts as $deliveryProduct) {
                $freeDelivery->addDeliveryProduct($deliveryProduct);
            }

            $delivery->setDeliveryProducts([]);
            $this->repo->save($delivery);
            $this->repo->save($freeDelivery, true);

            $this->logger->notice('[DELIVERIES] Delivery deleted.', [
                'delivery' => $id,
                'freeDelivery' => $freeDelivery->getid(),
                'products' => $deliveryProducts->map(fn ($dp) => $dp->getProduct()->getId())->toArray(),
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

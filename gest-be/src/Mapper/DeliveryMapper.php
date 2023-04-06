<?php

namespace App\Mapper;

use App\Entity\Delivery;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class DeliveryMapper
{
    public function __construct(
        private DeliveryProductMapper $deliveryProductMapper,
        private UserRepository $userRepository,
        )
    {}

    public function fromRequest(Request $request): Delivery
    {
        return $this->fill(new Delivery(), $request);
    }

    public function fill(Delivery $delivery, Request $request): Delivery
    {
        $data = $request->toArray();
        $deliveryProducts = $this->deliveryProductMapper->map($data['deliveryProducts'], $delivery);
        $customer = $this->userRepository->find($data['customer']['id']);

        return $delivery
            ->setCustomer($customer)
            ->setDeliveryProducts($deliveryProducts)
            ->setWeekDay($data['weekDay']);
    }
}

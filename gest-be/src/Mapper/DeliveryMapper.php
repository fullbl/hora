<?php

namespace App\Mapper;

use App\Entity\Delivery;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DeliveryMapper
{
    public function __construct(
        private SerializerInterface $serializer,
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
        $delivery = $this->serializer->deserialize(
            $request->getContent(), 
            Delivery::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $delivery]
        );

        $data = $request->toArray();
        $deliveryProducts = $this->deliveryProductMapper->map($data['deliveryProducts'], $delivery);
        $customer = $this->userRepository->find($data['customer']['id']);

        return $delivery
            ->setCustomer($customer)
            ->setDeliveryProducts($deliveryProducts);
    }
}

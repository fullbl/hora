<?php

namespace App\Mapper;

use App\Entity\Delivery;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DeliveryMapper
{
    public function __construct(
        private SerializerInterface $serializer,
        private DeliveryProductMapper $deliveryProductMapper,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return Generator<Delivery>
     */
    public function fromRequest(Request $request): Generator
    {
        $data = $request->toArray();
        foreach ($data['deliveryDates'] as $key => $deliveryDate) {
            $delivery = $this->fill(new Delivery(), $request);
            $delivery->setDeliveryDate(new \DateTimeImmutable($deliveryDate));
            $delivery->setHarvestDate(new \DateTimeImmutable($data['harvestDates'][$key]));
            yield $delivery;
        }
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
        if (isset($data['customer']['id'])) {
            $delivery->setCustomer(
                $this->em->getReference(User::class, $data['customer']['id'])
            );
        } else {
            $delivery->setCustomer(null);
        }

        return $delivery
            ->setDeliveryProducts($deliveryProducts);
    }
}

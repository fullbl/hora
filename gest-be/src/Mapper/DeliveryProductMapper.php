<?php

namespace App\Mapper;

use App\Entity\Delivery;
use App\Entity\DeliveryProduct;
use App\Entity\Product;
use App\Repository\DeliveryProductRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeliveryProductMapper
{
    public function __construct(
        private DeliveryProductRepository $deliveryProductRepository,
        private ProductRepository $productRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array<DeliveryProduct>
     */
    public function map(array $data, Delivery $delivery): array
    {
        return array_filter(
            array_map(
                fn (array $single): DeliveryProduct => $this->mapSingle($single)->setDelivery($delivery),
                $data
            ),
            fn (DeliveryProduct $dp) => $dp->getQty() > 0
        );
    }

    public function mapSingle(array $data): DeliveryProduct
    {
        $dp = new DeliveryProduct();
        $dp->setProduct($this->em->getReference(Product::class, $data['product']['id']));
        $dp->setQty($data['qty']);

        return $dp;
    }
}

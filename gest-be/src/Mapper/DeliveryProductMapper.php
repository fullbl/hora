<?php

namespace App\Mapper;

use App\Entity\Delivery;
use App\Entity\DeliveryProduct;
use App\Repository\DeliveryProductRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;

class DeliveryProductMapper
{
    public function __construct(
        private DeliveryProductRepository $deliveryProductRepository,
        private ProductRepository $productRepository,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @return array<DeliveryProduct>
     */
    public function map(array $data, Delivery $delivery): array
    {
        return array_map(
            fn(array $data): DeliveryProduct => $this->mapSingle($data)->setDelivery($delivery),
            $data
        );
    }

    public function mapSingle(array $data): DeliveryProduct
    {
        if (isset($data['id'])) {
            $dp = $this->deliveryProductRepository->find($data['id']);
        } else {
            $dp = new DeliveryProduct();
            $product = $this->productRepository->find($data['product']['id']);
            $dp->setProduct($product);
        }

        $dp->setQty($data['qty']);

        return $dp;
    }
}

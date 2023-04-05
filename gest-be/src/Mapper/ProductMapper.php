<?php

namespace App\Mapper;

use App\Entity\Product;
use App\Repository\StorageRepository;
use Symfony\Component\HttpFoundation\Request;

class ProductMapper
{
    public function __construct(private StorageRepository $storageRepository)
    {}

    public function fromRequest(Request $request): Product
    {
        return self::fill(new Product(), $request);
    }

    public function fill(Product $product, Request $request): Product
    {
        $data = $request->toArray();
        return $product
            ->setName($data['name'])
            ->setStorage($this->storageRepository->find($data['storage']['id']))
            ->setGrams($data['grams']);
    }
}

<?php

namespace App\Mapper;

use App\Entity\Horder;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;

class HorderMapper
{
    public function __construct(private ProductRepository $productRepository)
    {}

    public function fromRequest(Request $request): Horder
    {
        return $this->fill(new Horder(), $request);
    }

    public function fill(Horder $horder, Request $request): Horder
    {
        $data = $request->toArray();
        $product = $this->productRepository->find($data['product']['id']);

        return $horder
            ->setProduct($product)
            ->setQuantity($data['quantity'])
            ->setStatus($data['status']);
    }
}

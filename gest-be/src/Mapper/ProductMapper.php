<?php

namespace App\Mapper;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductMapper
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function fromRequest(Request $request): Product
    {
        return $this->fill(new Product(), $request);
    }

    public function fill(Product $product, Request $request): Product
    {
        return $this->serializer->deserialize(
            $request->getContent(), 
            Product::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $product]
        );
    }
}

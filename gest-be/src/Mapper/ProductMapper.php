<?php

namespace App\Mapper;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;

class ProductMapper
{
    public static function fromRequest(Request $request): Product
    {
        return self::fill(new Product(), $request);
    }

    public static function fill(Product $product, Request $request): Product
    {
        $data = $request->toArray();
        return $product
            ->setName($data['name'])
            ->setType($data['type'])
            ->setGrams($data['grams']);
    }
}

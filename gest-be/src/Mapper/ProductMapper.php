<?php

namespace App\Mapper;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;

class ProductMapper
{
    public static function fromRequest(Request $request): Product
    {
        return (new Product())
            ->setName($request->get('name'))
            ->setType($request->get('type'))
            ->setGrams($request->get('grams'));
    }

    public static function fill(Product $product, Request $request): Product
    {
        return $product
            ->setName($request->get('name'))
            ->setType($request->get('type'))
            ->setGrams($request->get('grams'));
    }
}

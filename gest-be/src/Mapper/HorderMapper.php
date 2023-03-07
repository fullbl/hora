<?php

namespace App\Mapper;

use App\Entity\Horder;
use Symfony\Component\HttpFoundation\Request;

class HorderMapper
{
    public static function fromRequest(Request $request): Horder
    {
        return (new Horder())
            ->setProduct($request->get('product'))
            ->setQuantity($request->get('quantity'))
            ->setStatus($request->get('status'));
    }

    public static function fill(Horder $horder, Request $request): Horder
    {
        return $horder
            ->setProduct($request->get('product'))
            ->setQuantity($request->get('quantity'))
            ->setStatus($request->get('status'));
    }
}

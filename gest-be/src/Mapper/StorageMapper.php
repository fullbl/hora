<?php

namespace App\Mapper;

use App\Entity\Storage;
use Symfony\Component\HttpFoundation\Request;

class StorageMapper
{
    public static function fromRequest(Request $request): Storage
    {
        return self::fill(new Storage(), $request);
    }

    public static function fill(Storage $storage, Request $request): Storage
    {
        $data = $request->toArray();
        return $storage
            ->setType($data['type'])
            ->setGrams($data['grams']);
    }
}

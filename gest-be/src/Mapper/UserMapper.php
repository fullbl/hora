<?php

namespace App\Mapper;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UserMapper
{
    public static function fromRequest(Request $request): User
    {
        return static::fill(new User(), $request);
    }

    public static function fill(User $user, Request $request): User
    {
        $data = $request->toArray();
        return $user
            ->setUsername($data['username'])
            ->setVatNumber($data['vatNumber'])
            ->setAddress($data['address'])
            ->setEmail($data['email'])
            ->setFullName($data['fullName'])
            ->setPassword($data['password'])
            ->setRoles($data['roles'])
            ->setStatus($data['status']);
    }
}

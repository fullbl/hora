<?php

namespace App\Mapper;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UserMapper
{
    public static function fromRequest(Request $request): User
    {
        return (new User())
            ->setAddress($request->get('address'))
            ->setEmail($request->get('email'))
            ->setFullName($request->get('full_name'))
            ->setPassword($request->get('password'))
            ->setRoles([$request->get('role')])
            ->setStatus($request->get('status'));
    }

    public static function fill(User $user, Request $request): User
    {
        return $user
            ->setAddress($request->get('address'))
            ->setEmail($request->get('email'))
            ->setFullName($request->get('full_name'))
            ->setPassword($request->get('password'))
            ->setRoles([$request->get('role')])
            ->setStatus($request->get('status'));
    }
}

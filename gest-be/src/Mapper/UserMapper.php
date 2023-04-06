<?php

namespace App\Mapper;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function fromRequest(Request $request): User
    {
        return $this->fill(new User(), $request);
    }

    public function fill(User $user, Request $request): User
    {
        $data = $request->toArray();
        if (isset($data['password'])) {
            $user->setPassword($this->hasher->hashPassword($user, $data['password']));
        }

        return $user
            ->setUsername($data['username'])
            ->setVatNumber($data['vatNumber'])
            ->setAddress($data['address'])
            ->setEmail($data['email'])
            ->setFullName($data['fullName'])
            ->setRoles($data['roles'])
            ->setStatus($data['status']);
    }
}

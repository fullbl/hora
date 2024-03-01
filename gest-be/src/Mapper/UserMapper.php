<?php

namespace App\Mapper;

use App\Entity\User;
use App\Entity\Zone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em
    )
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

        foreach($user->getZones() as $zone) {
            if(!in_array($zone->getId(), array_column($data['zones'], 'id'))) {
                $user->removeZone($zone);
            }
        }
        foreach($data['zones'] as $zoneData) {
            if(!isset($zoneData['id'])) {
                continue;
            }
            $zone = $this->em->find(Zone::class, $zoneData['id']);
            $user->addZone($zone);
        }

        return $user
            ->setUsername($data['username'])
            ->setVatNumber($data['vatNumber'])
            ->setSdi($data['sdi'] ?? null)
            ->setAddress($data['address'] ?? null)
            ->setEmail($data['email'])
            ->setFullName($data['fullName'])
            ->setRoles($data['roles'])
            ->setStatus($data['status'])
            ->setPosition($data['position'])
        ;
    }
}

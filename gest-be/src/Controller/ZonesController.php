<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ZonesController extends AbstractController
{
    public function __construct(
        private ZoneRepository $repo,
    ) {
    }

    #[Route('/zones', name: 'zones')]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (null === $user) {
            return $this->json(
                ['error' => 'not found'],
                Response::HTTP_FORBIDDEN,
            );
        }
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            $zones = $this->repo->findBy([], ['name' => 'ASC']);
        }
        else{
            $zones = $user->getZones();
            $zones = array_merge(
                $zones->toArray(),
                ... $zones->map(fn (Zone $zone) => $zone->getSubZones()->toArray())->toArray()
            );
        }

        return $this->json($zones, Response::HTTP_OK, [], ['groups' => 'zone-list']);
    }
}

<?php

namespace App\Controller;

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
        return $this->json($this->repo->findAll(), Response::HTTP_OK, [], ['groups' => 'zone-list']);
    }
}

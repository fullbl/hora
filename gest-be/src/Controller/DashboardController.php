<?php

namespace App\Controller;

use App\Repository\StorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_OPERATOR')]
class DashboardController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/dashboards/storage', methods: ['GET'], name: 'dashboard_storage')]
    public function storage(StorageRepository $storageRepository): JsonResponse
    {
        return $this->json($storageRepository->findAll());
    }

}

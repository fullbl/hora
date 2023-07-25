<?php

namespace App\Controller;

use App\Mapper\StepMapper;
use App\Repository\LogRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
class LogsController extends AbstractController
{
    public function __construct(
        private LogRepository $repo,
    ) {
    }

    #[Route('/logs/{entity}/{id}', methods: ['GET'], name: 'logs_list')]
    public function list(string $entity, int $id): JsonResponse
    {
        return $this->json($this->repo->findBy([
            'entityClass' => $entity,
            'entityId' => $id,
        ]));
    }
}

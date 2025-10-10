<?php

namespace App\Controller;

use App\Repository\VideoGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ApiVideoGameController extends AbstractController
{
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/v1/game', name: 'app_api_v1_game', methods: ['GET'])]
    public function index(VideoGameRepository $videoGameRepository, Request $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',2);
        
        $videoGames = $videoGameRepository->findAllWithPagination($page, $limit);

        return $this->json(
            $videoGames, 200, [], ['groups' => 'videogame:read']
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/add-game', name: 'app_api_add_game', methods: ['POST'])]
    public function apiV1AddGame(VideoGameRepository $videoGameRepository): JsonResponse
    {
        $videoGames = $videoGameRepository->findAll();

        return $this->json([
            $videoGames, 200, [], ['groups' => 'video_game_read']
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/edit-game/{id}', name: 'app_api_edit_game', methods: ['PUT'])]
    public function apiV1EditGame(VideoGameRepository $videoGameRepository, int $id): JsonResponse
    {
        $videoGames = $videoGameRepository->findAll();

        return $this->json([
            $videoGames, 200, [], ['groups' => 'video_game_read']
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/delete-game/{id}', name: 'app_api_delete_game', methods: ['DELETE'])]
    public function apiV1DeleteGame(VideoGameRepository $videoGameRepository, int $id): JsonResponse
    {
        $videoGames = $videoGameRepository->findAll();

        return $this->json([
            $videoGames, 200, [], ['groups' => 'video_game_read']
        ]);
    }
}

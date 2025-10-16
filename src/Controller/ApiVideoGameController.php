<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\CategoryRepository;
use App\Repository\EditorRepository;
use App\Repository\VideoGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/api/v1/add-game', name: 'app_api_add_game', methods: ['POST'])]
    public function apiV1AddGame(Request $request, EntityManagerInterface $em, EditorRepository $editorRepository, SerializerInterface $serializer, CategoryRepository $categoryRepository, ValidatorInterface $validator): JsonResponse 
    {
        //RECUPERATION DES DONNES DE LA REQUÊTE + DECODE
        $videoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json');

        //POUR DECODER LES RELATIONS 
        $data = json_decode($request->getContent(), true);
        
        //VERIFICATION ERREUR JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['editor_id'])) {
            return $this->json(['message' => "Le champ 'editor_id' est manquant."], Response::HTTP_BAD_REQUEST);
        }
        $editor = $editorRepository->find($data['editor_id']);
        if (!$editor) {
            return $this->json(['message' => "L'éditeur avec l'ID {$data['editor_id']} n'existe pas."], Response::HTTP_NOT_FOUND);
        }

        if (empty($data['category_id']) || !is_array($data['category_id'])) {
            return $this->json(['message' => "Le champ 'category_id' est manquant ou n'est pas un tableau."], Response::HTTP_BAD_REQUEST);
        }

        $categories = $categoryRepository->findBy(['id' => $data['category_id']]);
        if (count($categories) !== count($data['category_id'])) {
            return $this->json(['message' => "Une ou plusieurs catégories n'ont pas été trouvées."], Response::HTTP_NOT_FOUND);
        }

        //NOUVELLE OBJET VIDEOGAME
        $videoGame = new VideoGame();
        $videoGame->setTitle($data['title']);
        $videoGame->setDescription($data['description']);
        
        //GRSTION DE LA DATE
        if (!empty($data['release_date'])) {
            try {
                $videoGame->setReleaseDate(new \DateTime($data['release_date']));
            } catch (\Exception $e) {
                return $this->json(['message' => "Format de date invalide. Utilisez YYYY-MM-DD."], Response::HTTP_BAD_REQUEST);
            }
        }
        
        //ON PEUT AJOUTER A PLUSIEURS CATEGORY
        $videoGame->setEditor($editor);
        foreach ($categories as $category) {
            $videoGame->addCategory($category);
        }
        $videoGame->setCreatedAt(new \DateTimeImmutable());
        $videoGame->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($videoGame);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($videoGame);
        $em->flush();

        return $this->json($videoGame, Response::HTTP_CREATED, [], ['groups' => 'videogame:read']
        );
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

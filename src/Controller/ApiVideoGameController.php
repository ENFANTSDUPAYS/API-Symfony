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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/add-game', name: 'app_api_add_game', methods: ['POST'])]
    public function apiV1AddGame(Request $request, EntityManagerInterface $em, EditorRepository $editorRepository, SerializerInterface $serializer, CategoryRepository $categoryRepository, ValidatorInterface $validator): JsonResponse 
    {
        //RECUPERATION DES DONNES DE LA REQUÊTE + DECODE
        $videoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json');

        //POUR DECODER LES RELATIONS 
        $data = json_decode($request->getContent(), true);
        
        //VERIFICATION ERREUR JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }

        if (empty($data['editor_id'])) {
            return $this->json(['message' => "Le champ 'editor_id' est manquant."], 400);
        }
        $editor = $editorRepository->find($data['editor_id']);
        if (!$editor) {
            return $this->json(['message' => "L'éditeur avec l'ID {$data['editor_id']} n'existe pas."], 404);
        }
        //POUR S'ASSURER QUE C'EST DANS UN TABLEAU DANS POSTMAN
        if (!is_array($data['category_id'])) {
            return $this->json(['message' => "Le champ 'category_id' est manquant ou n'est pas un tableau."], 400);
        }

        $categories = $categoryRepository->findBy(['id' => $data['category_id']]);
        if (count($categories) !== count($data['category_id'])) {
            return $this->json(['message' => "Une ou plusieurs catégories n'ont pas été trouvées."], 404);
        }
        
        //GRSTION DE LA DATE
        if (!empty($data['release_date'])) {
            try {
                $videoGame->setReleaseDate(new \DateTime($data['release_date']));
            } catch (\Exception $e) {
                return $this->json(['message' => "Format de date invalide. Utilisez YYYY-MM-DD."], 400);
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
            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->persist($videoGame);
        $em->flush();

        return $this->json($videoGame, 201, [], ['groups' => 'videogame:read']
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/edit-game/{id}', name: 'app_api_edit_game', methods: ['PUT'])]
    public function apiV1EditGame(Request $request, VideoGame $videoGame, EntityManagerInterface $em, EditorRepository $editorRepository, CategoryRepository $categoryRepository,SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        //JSON VALIDEE
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 200);
        }

        if (isset($data['editor_id'])) {
            $editor = $editorRepository->find($data['editor_id']);
            if (!$editor) {
                return $this->json(['message' => "L'éditeur avec l'ID {$data['editor_id']} n'existe pas."], 404);
            }
            $videoGame->setEditor($editor);
        }

        if (isset($data['category_id'])) {
            //POUR S'ASSURER QUE C'EST DANS UN TABLEAU DANS POSTMAN
            if (!is_array($data['category_id'])) {
                return $this->json(['message' => "Le champ 'category_id' doit être un tableau."], 200);
            }
            
            foreach ($videoGame->getCategory() as $category) {
                $videoGame->removeCategory($category);
            }

            $categories = $categoryRepository->findBy(['id' => $data['category_id']]);
            if (count($categories) !== count($data['category_id'])) {
                return $this->json(['message' => "Une ou plusieurs catégories n'ont pas été trouvées."], 404);
            }
            foreach ($categories as $category) {
                $videoGame->addCategory($category);
            }
        }

        //VALIDATION DES VALIDATORS
        $errors = $validator->validate($videoGame);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        //MISE A JOUR DE LA DATE DE MODIFICATION
        $videoGame->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();
        
        return $this->json([$videoGame, 200, [], ['groups' => 'video_game_read']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/game-delete/{id}', name:'app_game_delete', methods: ['DELETE'])]
    public function apiV1DeleteEditor(VideoGame $videoGame, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($videoGame);
        $em->flush();

        return new JsonResponse(null, 204);
    }
}

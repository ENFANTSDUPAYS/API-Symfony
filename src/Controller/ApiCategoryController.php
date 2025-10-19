<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\VideoGame;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiCategoryController extends AbstractController
{
    #[Route('/api/v1/category', name: 'app_api_v1_category')]
    public function index(CategoryRepository $categoryRepository, Request $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',10);

        $categories = $categoryRepository->findAllWithPagination($page, $limit);

        if(!$categories) {
            return $this->json(["Aucune catégorie n'a été trouver."], 404);
        }

        return $this->json($categories,200, [], ['groups'=> 'category:read']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/add-category', name: 'app_api_add_category', methods: ['POST'])]
    public function apiV1AddCategory(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        json_decode($request->getContent());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }
        //DESERIALIZE LE JSON DANS L'OBJET EDITOR
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');

        //VALIDATION DE L'OBJET POUR VERIFIER LES CONTRAINTES
        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $category->setCreatedAt(new \DateTimeImmutable());
        $category->setUpdatedAt(new \DateTimeImmutable());
        
        $em->persist($category);
        $em->flush();

        return $this->json([$category, 201, [], ['groups' => 'category:read']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/category/{id}', name: 'app_api_edit_category', methods: ['PUT'])]
    public function apiV1EditCategory(Request $request, Category $category, EntityManagerInterface $em,SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse {
        
        // VERIFICATION PREALABLE QUE LE JSON EST VALIDE
        json_decode($request->getContent());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }

        // ON UTILISE LE SERIALIZER POUR METTRE A JOUR L'OBJET EXISTANT
        $serializer->deserialize($request->getContent(),Category::class,'json',['object_to_populate' => $category]);

        //VALIDATION DES VALIDATORS
        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        //MISE A JOUR DE LA DATE DE MODIFICATION
        $category->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json($category,200, [],['groups' => 'category:read']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/category-delete/{id}', name:'app_category_delete', methods: ['DELETE'])]
    public function apiV1DeleteEditor(Category $category, EntityManagerInterface $em): JsonResponse 
    {
        $category = $em->getRepository(Category::class)->find($category->getId());

        if (!$category) {
            return $this->json(['message'=> 'Aucune catégorie trouvée.'],404);
        }

        if(!$category->getVideoGames()->isEmpty()){
            return $this->json(['message'=> 'Cette categorie ne peut pas être supprimer car elle appartient à un video game'], 409);
        }
        
        $em->remove($category);
        $em->flush();

        return new JsonResponse(null, 204);
    }
}

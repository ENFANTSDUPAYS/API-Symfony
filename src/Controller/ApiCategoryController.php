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

final class ApiCategoryController extends AbstractController
{
    #[Route('/api/v1/category', name: 'app_api_v1_category')]
    public function index(CategoryRepository $categoryRepository, Request $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',10);
        $categories = $categoryRepository->findAllWithPagination($page, $limit);

        return $this->json($categories,200, [], ['groups'=> 'category:read']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/add-category', name: 'app_api_add_category', methods: ['POST'])]
    public function apiV1AddCategory(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class,'json');
        $em->persist($category);
        $em->flush();

        return $this->json([
            $category, Response::HTTP_CREATED, [], ['groups' => 'category:write']
        ]);
    }
}

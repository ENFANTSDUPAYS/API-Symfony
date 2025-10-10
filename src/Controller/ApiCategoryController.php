<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
}

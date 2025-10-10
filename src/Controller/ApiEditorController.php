<?php

namespace App\Controller;

use App\Repository\EditorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ApiEditorController extends AbstractController
{
    #[Route('/api/v1/editor', name: 'app_api_v1_editor')]
    public function index(EditorRepository $editorRepository, Request $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',10);
        $editors = $editorRepository->findAllWithPagination($page, $limit);

        return $this->json($editors,200, [], ['groups'=> 'editor:read']);
    }
}

<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Repository\EditorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/add-editor', name: 'app_api_add_editor', methods: ['POST'])]
    public function apiV1AddEditor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $editor = $serializer->deserialize($request->getContent(), Editor::class,'json');
        $editor->setCreatedAt(new \DateTimeImmutable());
        $editor->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($editor);
        $em->flush();

        return $this->json([
            $editor, Response::HTTP_CREATED, [], ['groups' => 'category:write']
        ]);
    }
}

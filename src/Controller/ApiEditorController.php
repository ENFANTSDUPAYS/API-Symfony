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
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function apiV1AddEditor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        
        json_decode($request->getContent());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }
        //DESERIALIZE LE JSON DANS L'OBJET EDITOR
        $editor = $serializer->deserialize($request->getContent(), Editor::class, 'json');

        //VALIDATION DE L'OBJET POUR VERIFIER LES CONTRAINTES
        $errors = $validator->validate($editor);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $editor->setCreatedAt(new \DateTimeImmutable());
        $editor->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($editor);
        $em->flush();

        return $this->json([
            $editor, 201, [], ['groups' => 'category:read']
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/editor/{id}', name: 'app_api_edit_editor', methods: ['PUT'])]
    public function apiV1EditEditor(Request $request, Editor $editor, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse {
        
        json_decode($request->getContent());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }

        //ON SERIALIZE POUR METTRE A JOUR L'OBJET EXISTANT + OBJECT_TO_POPULATE pour ne pas en créer u n nouveau
        $serializer->deserialize($request->getContent(),Editor::class,'json',['object_to_populate' => $editor]);

        //LA VALIDATION
        $errors = $validator->validate($editor);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        //MODIFICATION TOUJOURS DE UPDATED
        $editor->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json($editor,200,[],['groups' => 'editor:read']);
    }
}

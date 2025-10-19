<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Entity\User;
use App\Repository\EditorRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiUserController extends AbstractController
{
    #[Route('/api/v1/user', name: 'app_api_v1_user')]
    public function index(UserRepository $userRepository, Request $request): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit',10);

        $users = $userRepository->findAllWithPagination($page, $limit);

        if(!$users){
            return $this->json(["Aucun utilisateur n'a été trouver."], 404);
        }

        return $this->json($users,200, [], ['groups'=> 'user:read']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/add-user', name: 'app_api_add_user', methods: ['POST'])]
    public function apiV1AddUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }
        //DESERIALIZE LE JSON DANS L'OBJET USER
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        //HASH DU PASSWORD
        if(!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        //VALIDATION DE L'OBJET POUR VERIFIER LES CONTRAINTES
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json([$user, 201, [], ['groups' => 'user:read']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/user/{id}', name: 'app_api_edit_user', methods: ['PUT'])]
    public function apiV1EditUser(Request $request, User $user, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse {
        
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['message' => 'JSON invalide.'], 400);
        }

        //ON SERIALIZE POUR METTRE A JOUR L'OBJET EXISTANT + OBJECT_TO_POPULATE pour ne pas en créer u n nouveau
        $serializer->deserialize($request->getContent(),User::class,'json',['object_to_populate' => $user]);

        //HASH DU PASSWORD
        if(!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        //LA VALIDATION
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        //MODIFICATION TOUJOURS DE UPDATED
        $user->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json($user,200,[],['groups' => 'user:read']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/v1/user-delete/{id}', name:'app_user_delete', methods: ['DELETE'])]
    public function apiV1DeleteEditor(User $user, EntityManagerInterface $em): JsonResponse 
    {
        $user = $em->getRepository(User::class)->find($user->getId());
        
        if(!$user){
            return $this->json(['message'=> 'Aucun utilisateur trouvé'],404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, 204);
    }
}

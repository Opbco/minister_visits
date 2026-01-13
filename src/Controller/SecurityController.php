<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SecurityController extends AbstractController
{
    public function __construct(private RoleHierarchyInterface $roleHierarchy)
    {
    }

    #[Route(path: '/api/logout', name: 'api_logout', methods: ['POST'])]
    public function api_logout(): Response
    {
        $response = new Response();
        $response->headers->clearCookie('authToken', '/', null, true, true, 'Lax');

        return $this->json(['message' => 'Déconnexion réussie.']);
    }

    #[Route(path: '/api/me', name: 'api_me', methods: ['GET'])]
    public function getUserDetails(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser(); // Renvoie l'objet User authentifié par le JWT
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = array(
            'user'=> array(
                'id' => $user->getId(),
                'username' => $user->getUserIdentifier(),
                'email' => $user->getEmail(),
                'roles' => $this->roleHierarchy->getReachableRoleNames($user->getRealRoles()),
            ),
        );

        $response = $serializer->serialize($data, 'json', [
            'groups' => ['user.details']
        ]);

        return new JsonResponse($response, 200, [], true);
    }

    #[Route('/api/reset-password', name: 'app_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse 
    {
        
        try {
            $user = $this->getUser();
            $jsonData = json_decode($request->getContent(), true);
            $newPassword = $jsonData['password'];
            $confirmPassword = $jsonData['confirmPassword'];

            if ($newPassword !== $confirmPassword) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'New passwords do not match'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check that the new password is different from the old one
            if ($passwordHasher->isPasswordValid($user, $newPassword)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'New password is the same as the old one'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Update the password
            $user->setPlainPassword($newPassword);
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

            //persist changes
            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Password successfully updated'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating the password'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

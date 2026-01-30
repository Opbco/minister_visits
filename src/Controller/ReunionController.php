<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PersonnelRepository;
use App\Repository\ReunionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/reunions', name: 'api_reunions_')]
class ReunionController extends AbstractController
{
    public function __construct(
        private ReunionRepository $reunionRepository,
        private PersonnelRepository $personnelRepository,
        private SerializerInterface $serializer
    ) {
    }


    /**
     * Get reunion by its ID.
     * 
     * @Route: GET /api/reunions/{id}
     * 
     * @param int $Id The Reunion ID
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'reunion_by_id', methods: ['GET'])]
    public function getReunionById(int $id): JsonResponse
    {
        try {
            // Find reunion
            $reunion = $this->reunionRepository->find($id);

            if (!$reunion) {
                return $this->json([
                    'error' => 'Reunion not found',
                    'message' => sprintf('No reunion found with ID %d', $id)
                ], Response::HTTP_NOT_FOUND);
            }

            // Serialize the reunion
            $jsonContent = $this->serializer->serialize(
                $reunion,
                'json',
                ['groups' => ['reunion:read']]
            );

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
            
        } catch (\Exception $e) {
            return $this->json([
                    'error' => "An error occurred while fetching reunion",
                    'message' => $e->getMessage()
                ], Response::HTTP_NOT_FOUND);
        }
    }
    /**
     * Get all reunions accessible to a personnel by their user account ID.
     * 
     * @Route: GET /api/reunions/accessible/user/{userId}
     * 
     * @param int $userId The user account ID
     * @return JsonResponse
     */
    #[Route('/accessible/user/{userId}', name: 'accessible_by_user', methods: ['GET'])]
    public function getAccessibleReunionsByUser(int $userId): JsonResponse
    {
        try {
            // Find personnel by user account ID
            $personnel = $this->personnelRepository->findOneBy(['userAccount' => $userId]);

            if (!$personnel) {
                return $this->json([
                    'error' => 'Personnel not found for this user account',
                    'message' => sprintf('No personnel found with user account ID %d', $userId)
                ], Response::HTTP_NOT_FOUND);
            }

            // Get accessible reunions
            $reunions = $this->reunionRepository->findAccessibleByPersonnel($personnel);

            // Serialize the reunions with appropriate groups
            $jsonContent = $this->serializer->serialize(
                $reunions,
                'json',
                ['groups' => ['reunion:read']]
            );

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching reunions',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all reunions accessible to the currently authenticated personnel.
     * 
     * @Route: GET /api/reunions/accessible/me
     * 
     * @return JsonResponse
     */
    #[Route('/accessible/me', name: 'accessible_by_current_user', methods: ['GET'])]
    public function getAccessibleReunionsForCurrentUser(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            if (!$user) {
                return $this->json([
                    'error' => 'Authentication required',
                    'message' => 'You must be logged in to access this resource'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Find personnel by the authenticated user
            $personnel = $this->personnelRepository->findOneBy(['userAccount' => $user->getId()]);

            if (!$personnel) {
                return $this->json([
                    'error' => 'Personnel not found',
                    'message' => 'No personnel record found for the authenticated user'
                ], Response::HTTP_NOT_FOUND);
            }

            // Get accessible reunions
            $reunions = $this->reunionRepository->findAccessibleByPersonnel($personnel);

            // Serialize the reunions
            $jsonContent = $this->serializer->serialize(
                $reunions,
                'json',
                ['groups' => ['reunion:read']]
            );

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching reunions',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all reunions accessible to a personnel by their personnel ID.
     * 
     * @Route: GET /api/reunions/accessible/personnel/{personnelId}
     * 
     * @param int $personnelId The personnel ID
     * @return JsonResponse
     */
    #[Route('/accessible/personnel/{personnelId}', name: 'accessible_by_personnel', methods: ['GET'])]
    public function getAccessibleReunionsByPersonnel(int $personnelId): JsonResponse
    {
        try {
            // Find personnel by ID
            $personnel = $this->personnelRepository->find($personnelId);

            if (!$personnel) {
                return $this->json([
                    'error' => 'Personnel not found',
                    'message' => sprintf('No personnel found with ID %d', $personnelId)
                ], Response::HTTP_NOT_FOUND);
            }

            // Get accessible reunions
            $reunions = $this->reunionRepository->findAccessibleByPersonnel($personnel);

            // Serialize the reunions
            $jsonContent = $this->serializer->serialize(
                $reunions,
                'json',
                ['groups' => ['reunion:read']]
            );

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching reunions',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get accessible reunions with additional filters (date range, status).
     * 
     * @Route: GET /api/reunions/accessible/user/{userId}/filtered
     * 
     * @param int $userId The user account ID
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/accessible/user/{userId}/filtered', name: 'accessible_by_user_filtered', methods: ['GET'])]
    public function getFilteredAccessibleReunions(int $userId, Request $request): JsonResponse
    {
        try {
            // Find personnel by user account ID
            $personnel = $this->personnelRepository->findOneBy(['userAccount' => $userId]);

            if (!$personnel) {
                return $this->json([
                    'error' => 'Personnel not found for this user account',
                    'message' => sprintf('No personnel found with user account ID %d', $userId)
                ], Response::HTTP_NOT_FOUND);
            }

            // Get accessible reunions
            $reunions = $this->reunionRepository->findAccessibleByPersonnel($personnel);

            // Apply filters from query parameters
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $status = $request->query->get('status');
            $limit = $request->query->getInt('limit', 50);

            // Filter the results
            $filteredReunions = array_filter($reunions, function ($reunion) use ($startDate, $endDate, $status) {
                $matches = true;

                // Filter by start date
                if ($startDate && $reunion->getDateDebut() < new \DateTime($startDate)) {
                    $matches = false;
                }

                // Filter by end date
                if ($endDate && $reunion->getDateDebut() > new \DateTime($endDate)) {
                    $matches = false;
                }

                // Filter by status
                if ($status !== null && $reunion->getStatut()->value != $status) {
                    $matches = false;
                }

                return $matches;
            });

            // Apply limit
            $filteredReunions = array_slice($filteredReunions, 0, $limit);

            // Serialize the reunions
            $jsonContent = $this->serializer->serialize(
                array_values($filteredReunions), // Re-index array
                'json',
                ['groups' => ['reunion:read']]
            );

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching reunions',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get statistics about accessible reunions for a user.
     * 
     * @Route: GET /api/reunions/accessible/user/{userId}/stats
     * 
     * @param int $userId The user account ID
     * @return JsonResponse
     */
    #[Route('/accessible/user/{userId}/stats', name: 'accessible_stats', methods: ['GET'])]
    public function getAccessibleReunionsStats(int $userId): JsonResponse
    {
        try {
            // Find personnel by user account ID
            $personnel = $this->personnelRepository->findOneBy(['userAccount' => $userId]);

            if (!$personnel) {
                return $this->json([
                    'error' => 'Personnel not found for this user account',
                    'message' => sprintf('No personnel found with user account ID %d', $userId)
                ], Response::HTTP_NOT_FOUND);
            }

            // Get accessible reunions
            $reunions = $this->reunionRepository->findAccessibleByPersonnel($personnel);

            // Calculate statistics
            $total = count($reunions);
            $byStatus = [];
            $upcoming = 0;
            $past = 0;
            $today = new \DateTime('today');

            foreach ($reunions as $reunion) {
                // Count by status
                $statusValue = $reunion->getStatut()->value;
                $byStatus[$statusValue] = ($byStatus[$statusValue] ?? 0) + 1;

                // Count upcoming vs past
                if ($reunion->getDateDebut() >= $today) {
                    $upcoming++;
                } else {
                    $past++;
                }
            }

            return $this->json([
                'personnel' => [
                    'id' => $personnel->getId(),
                    'name' => $personnel->getNomComplet(),
                    'structure' => $personnel->getStructure()?->getNameFr(),
                ],
                'statistics' => [
                    'total' => $total,
                    'upcoming' => $upcoming,
                    'past' => $past,
                    'byStatus' => $byStatus,
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while calculating statistics',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get only direct participations (not organizational or hierarchical).
     * 
     * @Route: GET /api/reunions/participations/user/{userId}
     * 
     * @param int $userId The user account ID
     * @return JsonResponse
     */
    #[Route('/participations/user/{userId}', name: 'direct_participations', methods: ['GET'])]
    public function getDirectParticipations(int $userId): JsonResponse
    {
        try {
            // Find personnel by user account ID
            $personnel = $this->personnelRepository->findOneBy(['userAccount' => $userId]);

            if (!$personnel) {
                return $this->json([
                    'error' => 'Personnel not found for this user account',
                    'message' => sprintf('No personnel found with user account ID %d', $userId)
                ], Response::HTTP_NOT_FOUND);
            }

            // Get only direct participations
            $reunions = $this->reunionRepository->findByParticipant($personnel);

            // Serialize the reunions
            $jsonContent = $this->serializer->serialize(
                $reunions,
                'json',
                ['groups' => ['reunion:read']]
            );

            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching participations',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
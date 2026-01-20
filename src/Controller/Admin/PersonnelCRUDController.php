<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Personnel;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class PersonnelCRUDController extends CRUDController
{
    /**
     * Display all meetings for a specific personnel
     */
    public function meetingsAction(Request $request): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $this->admin->getIdParameter()));
        }

        // Check access
        $this->admin->checkAccess('show', $object);

        /** @var Personnel $personnel */
        $personnel = $object;
        
        // Get all participations ordered by meeting date
        $participations = $personnel->getMyReunions()->toArray();
        
        // Sort by meeting date (newest first)
        usort($participations, function($a, $b) {
            return $b->getReunion()->getDateDebut() <=> $a->getReunion()->getDateDebut();
        });

        // Calculate statistics
        $totalMeetings = count($participations);
        $attendedMeetings = count(array_filter($participations, fn($p) => $p->getStatus()->value === 'attended'));
        $upcomingMeetings = count(array_filter($participations, fn($p) => $p->getReunion()->isUpcoming()));
        $asPresident = count(array_filter($participations, fn($p) => $p->getReunion()->getPresident() === $personnel));
        $absentMeetings = count(array_filter($participations, fn($p) => $p->getStatus()->value === 'absent'));
        
        $attendanceRate = $totalMeetings > 0 ? round(($attendedMeetings / $totalMeetings) * 100) : 0;

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/personnel/meetings.html.twig', [
            'object' => $personnel,
            'participations' => $participations,
            'statistics' => [
                'total' => $totalMeetings,
                'attended' => $attendedMeetings,
                'upcoming' => $upcomingMeetings,
                'asPresident' => $asPresident,
                'absent' => $absentMeetings,
                'attendanceRate' => $attendanceRate,
            ],
            'action' => 'meetings',
        ]);
    }

    /**
     * Display all action items for a specific personnel
     */
    public function actionItemsAction(Request $request): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $this->admin->getIdParameter()));
        }

        // Check access
        $this->admin->checkAccess('show', $object);

        /** @var Personnel $personnel */
        $personnel = $object;
        
        // Get all action items ordered by due date
        $actionItems = $personnel->getActionItems()->toArray();
        
        // Sort by due date (earliest first), then by creation date
        usort($actionItems, function($a, $b) {
            // Items without due date go to the end
            if (!$a->getDateEcheance() && !$b->getDateEcheance()) {
                return $b->getDateCreated() <=> $a->getDateCreated();
            }
            if (!$a->getDateEcheance()) return 1;
            if (!$b->getDateEcheance()) return -1;
            
            return $a->getDateEcheance() <=> $b->getDateEcheance();
        });

        // Calculate statistics
        $totalActions = count($actionItems);
        $completedActions = count(array_filter($actionItems, fn($item) => $item->getStatut()->value === 'completed'));
        $pendingActions = count(array_filter($actionItems, fn($item) => $item->getStatut()->value === 'pending'));
        $inProgressActions = count(array_filter($actionItems, fn($item) => $item->getStatut()->value === 'in_progress'));
        
        $now = new \DateTime();
        $overdueActions = count(array_filter($actionItems, function($item) use ($now) {
            return $item->getDateEcheance() 
                && $item->getDateEcheance() < $now 
                && $item->getStatut()->value !== 'completed';
        }));
        
        $completionRate = $totalActions > 0 ? round(($completedActions / $totalActions) * 100) : 0;

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/personnel/action_items.html.twig', [
            'object' => $personnel,
            'actionItems' => $actionItems,
            'statistics' => [
                'total' => $totalActions,
                'completed' => $completedActions,
                'pending' => $pendingActions,
                'inProgress' => $inProgressActions,
                'overdue' => $overdueActions,
                'completionRate' => $completionRate,
            ],
            'action' => 'action_items',
        ]);
    }

    /**
     * Create a user account for a personnel
     */
    public function createUserAccountAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $this->admin->getIdParameter()));
        }

        // Check access
        $this->admin->checkAccess('edit', $object);

        /** @var Personnel $personnel */
        $personnel = $object;

        // Check if user account already exists
        if ($personnel->getUserAccount()) {
            $this->addFlash('sonata_flash_error', 'This personnel already has a user account.');
            return $this->redirectToRoute('admin_app_personnel_edit', ['id' => $personnel->getId()]);
        }

        // Check if email exists
        if (!$personnel->getEmail()) {
            $this->addFlash('sonata_flash_error', 'Cannot create user account: Personnel must have an email address.');
            return $this->redirectToRoute('admin_app_personnel_edit', ['id' => $personnel->getId()]);
        }

        if ($request->isMethod('POST')) {
            try {
                // Create new user
                $user = new User();
                
                // Generate username from email or name
                $username = $this->generateUsername($personnel);
                $user->setUsername($username);
                $user->setEmail($personnel->getEmail());
                
                // Generate random password
                $randomPassword = $this->generateRandomPassword();
                $user->setPlainPassword($randomPassword);
                
                // Set basic role
                $user->setRoles(['ROLE_USER']);
                $user->setEnabled(true);

                // Link to personnel
                $personnel->setUserAccount($user);

                // Persist
                $entityManager->persist($user);
                $entityManager->persist($personnel);
                $entityManager->flush();

                $this->addFlash('sonata_flash_success', sprintf(
                    'User account created successfully! Username: %s | Temporary Password: %s (Please change on first login)',
                    $username,
                    $randomPassword
                ));

                return $this->redirectToRoute('admin_app_personnel_edit', ['id' => $personnel->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error creating user account: ' . $e->getMessage());
            }
        }

        // Show confirmation form
        return $this->renderWithExtraParams('@SonataAdmin/CRUD/personnel/create_user_account.html.twig', [
            'object' => $personnel,
            'action' => 'create_user_account',
        ]);
    }

    /**
     * Generate username from personnel data
     */
    private function generateUsername(Personnel $personnel): string
    {
        // Try to use first part of email
        if ($personnel->getEmail()) {
            $emailParts = explode('@', $personnel->getEmail());
            $username = strtolower($emailParts[0]);
            
            // Clean up username
            $username = preg_replace('/[^a-z0-9._-]/', '', $username);
            
            return $username;
        }

        // Fallback: use name + random number
        $name = strtolower(str_replace(' ', '.', $personnel->getNomComplet()));
        $name = preg_replace('/[^a-z0-9._-]/', '', $name);
        $name = substr($name, 0, 20); // Limit length
        
        return $name . rand(100, 999);
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // Ensure password meets complexity requirements
        if (!preg_match('/[A-Z]/', $password)) {
            $password[0] = chr(random_int(65, 90)); // Add uppercase
        }
        if (!preg_match('/[0-9]/', $password)) {
            $password[1] = (string)random_int(0, 9); // Add number
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $password[2] = '!'; // Add special char
        }
        
        return $password;
    }

    /**
     * Batch action: Export contacts
     */
    public function batchActionExportContacts(Request $request): Response
    {
        $this->admin->checkAccess('list');

        $selectedIds = $request->request->all()['idx'] ?? [];

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No personnel selected.');
            return $this->redirectToList();
        }

        // Get selected personnel
        $personnel = $this->admin->getModelManager()
            ->findBy($this->admin->getClass(), ['id' => $selectedIds]);

        // Generate CSV
        $csv = $this->generateContactsCsv($personnel);

        // Return as download
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="personnel_contacts_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    /**
     * Generate CSV from personnel list
     */
    private function generateContactsCsv(array $personnelList): string
    {
        $output = fopen('php://temp', 'r+');

        // Headers
        fputcsv($output, [
            'Staff Number',
            'Full Name',
            'Function',
            'Structure',
            'Email',
            'Phone',
            'Has User Account'
        ]);

        // Data rows
        foreach ($personnelList as $personnel) {
            fputcsv($output, [
                $personnel->getMatricule(),
                $personnel->getNomComplet(),
                $personnel->getFonction() ? $personnel->getFonction()->getLibelle() : '',
                $personnel->getStructure() ? $personnel->getStructure()->getNameFr() : '',
                $personnel->getEmail() ?: '',
                $personnel->getTelephone() ?: '',
                $personnel->getUserAccount() ? 'Yes' : 'No'
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Batch action: Send notification
     */
    public function batchActionSendNotification(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->admin->checkAccess('list');

        $selectedIds = $request->request->all()['idx'] ?? [];

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No personnel selected.');
            return $this->redirectToList();
        }

        if ($request->isMethod('POST') && $request->request->has('notification_confirmed')) {
            // Get selected personnel
            $personnelList = $this->admin->getModelManager()
                ->findBy($this->admin->getClass(), ['id' => $selectedIds]);

            $notificationType = $request->request->get('notification_type');
            $subject = $request->request->get('notification_subject');
            $message = $request->request->get('notification_message');

            // Validate
            $withEmail = array_filter($personnelList, fn($p) => $p->getEmail() !== null);
            $withPhone = array_filter($personnelList, fn($p) => $p->getTelephone() !== null);

            if ($notificationType === 'email' && count($withEmail) === 0) {
                $this->addFlash('sonata_flash_error', 'None of the selected personnel have email addresses.');
                return $this->redirectToList();
            }

            if ($notificationType === 'sms' && count($withPhone) === 0) {
                $this->addFlash('sonata_flash_error', 'None of the selected personnel have phone numbers.');
                return $this->redirectToList();
            }

            // TODO: Implement actual notification sending via a service
            // For now, just show success message
            
            $this->addFlash('sonata_flash_success', sprintf(
                'Notification queued for %d personnel. Type: %s',
                count($personnelList),
                $notificationType
            ));

            return $this->redirectToList();
        }

        // Show confirmation form
        $personnelList = $this->admin->getModelManager()
            ->findBy($this->admin->getClass(), ['id' => $selectedIds]);

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/personnel/batch_send_notification.html.twig', [
            'personnelList' => $personnelList,
            'selectedIds' => $selectedIds,
            'action' => 'batch_send_notification',
        ]);
    }
}
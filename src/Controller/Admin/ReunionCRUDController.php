<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Reunion;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\ReunionStatut;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReunionCRUDController extends CRUDController
{
    /**
     * Validate a meeting (change status to CONFIRMED)
     */
    public function validateAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Meeting not found');
        }

        $this->admin->checkAccess('edit', $object);

        /** @var Reunion $reunion */
        $reunion = $object;

        // Check if already validated
        if ($reunion->getStatut() === ReunionStatut::CONFIRMED) {
            $this->addFlash('sonata_flash_info', 'This meeting is already validated.');
            return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);
        }

        if ($request->isMethod('POST')) {
            try {
                // Validate the meeting
                $currentUser = $this->getUser();
                $reunion->validate($currentUser);

                $entityManager->flush();

                $this->addFlash('sonata_flash_success', sprintf(
                    'Meeting "%s" has been validated successfully.',
                    $reunion->getObjet()
                ));

                return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error validating meeting: ' . $e->getMessage());
            }
        }

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/reunion/validate.html.twig', [
            'object' => $reunion,
            'action' => 'validate',
        ]);
    }

    /**
     * Cancel a meeting
     */
    public function cancelAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Meeting not found');
        }

        $this->admin->checkAccess('edit', $object);

        /** @var Reunion $reunion */
        $reunion = $object;

        // Check if meeting can be cancelled
        if ($reunion->isPast()) {
            $this->addFlash('sonata_flash_error', 'Cannot cancel a past meeting.');
            return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);
        }

        if ($request->isMethod('POST')) {
            $reason = $request->request->get('cancel_reason');

            if (empty($reason)) {
                $this->addFlash('sonata_flash_error', 'Please provide a reason for cancellation.');
                return $this->redirectToRoute('admin_app_reunion_cancel', ['id' => $reunion->getId()]);
            }

            try {
                $reunion->setStatut(ReunionStatut::CANCELLED);
                $reunion->setMotifReport($reason); // Using motifReport field for cancel reason

                $entityManager->flush();

                $this->addFlash('sonata_flash_success', sprintf(
                    'Meeting "%s" has been cancelled.',
                    $reunion->getObjet()
                ));

                // TODO: Send cancellation notifications to participants

                return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error cancelling meeting: ' . $e->getMessage());
            }
        }

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/reunion/cancel.html.twig', [
            'object' => $reunion,
            'action' => 'cancel',
        ]);
    }

    /**
     * Postpone a meeting to a new date
     */
    public function postponeAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Meeting not found');
        }

        $this->admin->checkAccess('edit', $object);

        /** @var Reunion $reunion */
        $reunion = $object;

        if ($request->isMethod('POST')) {
            $newDateStr = $request->request->get('new_date');
            $reason = $request->request->get('postpone_reason');

            if (empty($newDateStr) || empty($reason)) {
                $this->addFlash('sonata_flash_error', 'Please provide both new date and reason for postponement.');
                return $this->redirectToRoute('admin_app_reunion_postpone', ['id' => $reunion->getId()]);
            }

            try {
                $newDate = new \DateTime($newDateStr);
                
                // Validate new date is in the future
                if ($newDate <= new \DateTime()) {
                    $this->addFlash('sonata_flash_error', 'New date must be in the future.');
                    return $this->redirectToRoute('admin_app_reunion_postpone', ['id' => $reunion->getId()]);
                }

                $reunion->postpone($newDate, $reason);

                $entityManager->flush();

                $this->addFlash('sonata_flash_success', sprintf(
                    'Meeting "%s" has been postponed to %s.',
                    $reunion->getObjet(),
                    $newDate->format('d/m/Y H:i')
                ));

                // TODO: Send postponement notifications to participants

                return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error postponing meeting: ' . $e->getMessage());
            }
        }

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/reunion/postpone.html.twig', [
            'object' => $reunion,
            'action' => 'postpone',
        ]);
    }

    /**
     * Mark meeting as completed
     */
    public function completeAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Meeting not found');
        }

        $this->admin->checkAccess('edit', $object);

        /** @var Reunion $reunion */
        $reunion = $object;

        // Check if meeting has happened
        if ($reunion->isUpcoming()) {
            $this->addFlash('sonata_flash_error', 'Cannot mark an upcoming meeting as completed.');
            return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);
        }

        if ($request->isMethod('POST')) {
            try {
                $reunion->complete();

                $entityManager->flush();

                $this->addFlash('sonata_flash_success', sprintf(
                    'Meeting "%s" has been marked as completed.',
                    $reunion->getObjet()
                ));

                return $this->redirectToRoute('admin_app_reunion_edit', ['id' => $reunion->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error completing meeting: ' . $e->getMessage());
            }
        }

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/reunion/complete.html.twig', [
            'object' => $reunion,
            'action' => 'complete',
        ]);
    }

    /**
     * Send invitations to all participants
     */
    public function sendInvitationsAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Meeting not found');
        }

        $this->admin->checkAccess('edit', $object);

        /** @var Reunion $reunion */
        $reunion = $object;

        // Check if meeting has participants
        if ($reunion->getParticipations()->count() === 0) {
            $this->addFlash('sonata_flash_error', 'Cannot send invitations: No participants added to this meeting.');
            return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);
        }

        if ($request->isMethod('POST')) {
            $notificationType = $request->request->get('notification_type', 'email');

            try {
                $sentCount = 0;
                $failedCount = 0;

                foreach ($reunion->getParticipations() as $participation) {
                    // Create notification for each participant
                    $notification = new Notification();
                    $notification->setReunion($reunion);
                    $notification->setType(NotificationType::from($notificationType));
                    
                    if ($participation->getPersonnel()) {
                        $notification->setPersonnel($participation->getPersonnel());
                    } elseif ($participation->getExternalParticipant()) {
                        $notification->setExternalParticipant($participation->getExternalParticipant());
                    }

                    // Set notification content
                    $notification->setSubject(sprintf('Invitation: %s', $reunion->getObjet()));
                    $notification->setMessage($this->generateInvitationMessage($reunion));
                    
                    $notification->setUserCreated($this->getUser());

                    // Validate recipient has required contact info
                    if ($notification->hasValidRecipient()) {
                        $entityManager->persist($notification);
                        $sentCount++;
                    } else {
                        $failedCount++;
                    }
                }

                $entityManager->flush();

                $this->addFlash('sonata_flash_success', sprintf(
                    'Invitations queued successfully! %d sent, %d failed (missing contact info).',
                    $sentCount,
                    $failedCount
                ));

                return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error sending invitations: ' . $e->getMessage());
            }
        }

        // Get statistics about participant contact info
        $stats = $this->getParticipantContactStats($reunion);

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/reunion/send_invitations.html.twig', [
            'object' => $reunion,
            'stats' => $stats,
            'action' => 'send_invitations',
        ]);
    }

    /**
     * Manage attendance tracking
     */
    public function attendanceAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Meeting not found');
        }

        $this->admin->checkAccess('edit', $object);

        /** @var Reunion $reunion */
        $reunion = $object;

        if ($request->isMethod('POST')) {
            $attendanceData = $request->request->all('attendance');

            try {
                foreach ($reunion->getParticipations() as $participation) {
                    $participationId = $participation->getId();
                    
                    if (isset($attendanceData[$participationId])) {
                        $status = $attendanceData[$participationId]['status'];
                        $reason = $attendanceData[$participationId]['reason'] ?? null;

                        $participation->setStatus(\App\Enum\ParticipantStatut::from($status));
                        
                        if ($reason) {
                            $participation->setAbsenceReason($reason);
                        }
                    }
                }

                $entityManager->flush();

                $this->addFlash('sonata_flash_success', 'Attendance has been updated successfully.');

                return $this->redirectToRoute('admin_app_reunion_show', ['id' => $reunion->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('sonata_flash_error', 'Error updating attendance: ' . $e->getMessage());
            }
        }

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/reunion/attendance.html.twig', [
            'object' => $reunion,
            'action' => 'attendance',
        ]);
    }

    /**
     * Generate invitation message
     */
    private function generateInvitationMessage(Reunion $reunion): string
    {
        $message = sprintf(
            "You are invited to:\n\n" .
            "Subject: %s\n" .
            "Date: %s\n" .
            "Time: %s - %s\n" .
            "Format: %s\n",
            $reunion->getObjet(),
            $reunion->getDateDebut()->format('l, F j, Y'),
            $reunion->getDateDebut()->format('H:i'),
            $reunion->getDateFin()->format('H:i'),
            $reunion->getMeetingType()->label()
        );

        // Add physical location if applicable
        if ($reunion->requiresPhysicalLocation()) {
            $location = $reunion->getSalle() 
                ? $reunion->getSalle()->getNom() 
                : ($reunion->getLieu() ?? 'Location TBD');
            $message .= sprintf("Location: %s\n", $location);
        }

        // Add video conference details if applicable
        if ($reunion->requiresVideoConference() && $reunion->getVideoConferenceLink()) {
            $message .= "\n--- Video Conference Details ---\n";
            $message .= sprintf(
                "Platform: %s\n" .
                "Join Link: %s\n",
                $reunion->getVideoConferencePlatform()?->label() ?? 'Video Conference',
                $reunion->getVideoConferenceLink()
            );

            if ($reunion->getVideoConferenceMeetingId()) {
                $message .= sprintf("Meeting ID: %s\n", $reunion->getVideoConferenceMeetingId());
            }

            if ($reunion->getVideoConferencePassword()) {
                $message .= sprintf("Password: %s\n", $reunion->getVideoConferencePassword());
            }

            if ($reunion->getVideoConferenceInstructions()) {
                $message .= sprintf("\nInstructions:\n%s\n", $reunion->getVideoConferenceInstructions());
            }
        }

        $message .= sprintf(
            "\nPlease confirm your attendance.\n\n" .
            "Organizer: %s",
            $reunion->getOrganisateur()->getNameFr()
        );

        return $message;
    }

    /**
     * Get participant contact statistics
     */
    private function getParticipantContactStats(Reunion $reunion): array
    {
        $stats = [
            'total' => $reunion->getParticipations()->count(),
            'with_email' => 0,
            'with_phone' => 0,
            'with_both' => 0,
            'without_contact' => 0,
        ];

        foreach ($reunion->getParticipations() as $participation) {
            $hasEmail = false;
            $hasPhone = false;

            if ($participation->getPersonnel()) {
                $hasEmail = !empty($participation->getPersonnel()->getEmail());
                $hasPhone = !empty($participation->getPersonnel()->getTelephone());
            } elseif ($participation->getExternalParticipant()) {
                $hasEmail = !empty($participation->getExternalParticipant()->getEmail());
                $hasPhone = !empty($participation->getExternalParticipant()->getTelephone());
            }

            if ($hasEmail && $hasPhone) {
                $stats['with_both']++;
                $stats['with_email']++;
                $stats['with_phone']++;
            } elseif ($hasEmail) {
                $stats['with_email']++;
            } elseif ($hasPhone) {
                $stats['with_phone']++;
            } else {
                $stats['without_contact']++;
            }
        }

        return $stats;
    }
}
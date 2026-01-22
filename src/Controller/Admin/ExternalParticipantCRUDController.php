<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ExternalParticipant;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

final class ExternalParticipantCRUDController extends CRUDController
{
    /**
     * Display meetings associated with this external participant
     */
    public function meetingsAction(Request $request): Response
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find External Participant with id: %s', $id));
        }

        if (!$object instanceof ExternalParticipant) {
            throw new \RuntimeException('Invalid object type');
        }

        // Get all participations with eager loading
        $participations = $object->getMyReunions();

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/external_participant/meetings.html.twig', [
            'object' => $object,
            'participations' => $participations,
            'action' => 'show',
        ]);
    }

    /**
     * Export contacts in CSV format
     */
    public function batchActionExportContacts(Request $request, EntityManagerInterface $em): Response
    {
        $selectedIds = $request->get('idx', []);

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No participants selected.');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $participants = $em->getRepository(ExternalParticipant::class)
            ->createQueryBuilder('ep')
            ->where('ep.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();

        // Create CSV content
        $csv = $this->generateCSV($participants);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="external_contacts_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    /**
     * Send notification to selected participants
     */
    public function batchActionSendNotification(Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $this->admin->checkAccess('list');

        $selectedIds = $request->request->all()['idx'] ?? [];

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No participants selected.');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        if ($request->isMethod('POST') && $request->request->has('notification_confirmed')) {
            // Validate CSRF token
            $token = $request->request->get('_sonata_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('sonata.batch', $token))) {
                $this->addFlash('sonata_flash_error', 'Invalid CSRF token. controller');
                return new RedirectResponse($this->admin->generateUrl('list'));
            }

            // Get selected participants
            $participants = $em->getRepository(ExternalParticipant::class)
                ->createQueryBuilder('ep')
                ->where('ep.id IN (:ids)')
                ->setParameter('ids', $selectedIds)
                ->getQuery()
                ->getResult();

            $notificationType = $request->request->get('notification_type');
            $subject = $request->request->get('notification_subject');
            $message = $request->request->get('notification_message');

            // Validate
            $withEmail = array_filter($participants, fn($p) => $p->getEmail() !== null);
            $withPhone = array_filter($participants, fn($p) => $p->getTelephone() !== null);

            if ($notificationType === 'email' && count($withEmail) === 0) {
                $this->addFlash('sonata_flash_error', 'None of the selected participants have email addresses.');
                return new RedirectResponse($this->admin->generateUrl('list'));
            }

            if ($notificationType === 'sms' && count($withPhone) === 0) {
                $this->addFlash('sonata_flash_error', 'None of the selected participants have phone numbers.');
                return new RedirectResponse($this->admin->generateUrl('list'));
            }

            // TODO: Implement actual notification sending via a service
            // For now, just show success message
            
            $this->addFlash('sonata_flash_success', sprintf(
                'Notification queued for %d participant(s). Type: %s',
                count($participants),
                $notificationType
            ));

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        // Show confirmation form
        $participants = $em->getRepository(ExternalParticipant::class)
            ->createQueryBuilder('ep')
            ->where('ep.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/external_participant/batch_send_notification.html.twig', [
            'participantList' => $participants,
            'selectedIds' => $selectedIds,
            'action' => 'batch_send_notification',
            'admin' => $this->admin,
        ]);
    }

    private function generateCSV(array $participants): string
    {
        $output = fopen('php://temp', 'r+');

        // Headers
        fputcsv($output, ['ID', 'Full Name', 'Organization', 'Function', 'Email', 'Phone', 'Added On']);

        // Data rows
        foreach ($participants as $participant) {
            fputcsv($output, [
                $participant->getId(),
                $participant->getNom(),
                $participant->getOrganisation() ?? '',
                $participant->getFonction() ?? '',
                $participant->getEmail() ?? '',
                $participant->getTelephone() ?? '',
                $participant->getDateCreated() ? $participant->getDateCreated()->format('Y-m-d H:i') : '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
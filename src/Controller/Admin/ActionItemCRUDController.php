<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ActionItem;
use App\Enum\ActionStatut;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ActionItemCRUDController extends CRUDController
{
    /**
     * Mark action item as completed
     */
    public function markCompletedAction(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find Action Item with id: %s', $id));
        }

        if (!$object instanceof ActionItem) {
            throw new \RuntimeException('Invalid object type');
        }

        $object->setStatut(ActionStatut::COMPLETED);
        $object->setDateUpdated(new \DateTime());

        $em->flush();

        $this->addFlash('sonata_flash_success', 'Action item marked as completed.');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Mark action item as in progress
     */
    public function markInProgressAction(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find Action Item with id: %s', $id));
        }

        if (!$object instanceof ActionItem) {
            throw new \RuntimeException('Invalid object type');
        }

        $object->setStatut(ActionStatut::IN_PROGRESS);
        $object->setDateUpdated(new \DateTime());

        $em->flush();

        $this->addFlash('sonata_flash_success', 'Action item marked as in progress.');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Batch action: Mark multiple items as completed
     */
    public function batchActionMarkCompleted(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $selectedIds = $request->get('idx', []);

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No action items selected.');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $actionItems = $em->getRepository(ActionItem::class)
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($actionItems as $actionItem) {
            $actionItem->setStatut(ActionStatut::COMPLETED);
            $actionItem->setDateUpdated(new \DateTime());
            $count++;
        }

        $em->flush();

        $this->addFlash('sonata_flash_success', sprintf('%d action item(s) marked as completed.', $count));

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Batch action: Mark multiple items as in progress
     */
    public function batchActionMarkInProgress(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $selectedIds = $request->get('idx', []);

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No action items selected.');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $actionItems = $em->getRepository(ActionItem::class)
            ->createQueryBuilder('a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($actionItems as $actionItem) {
            $actionItem->setStatut(ActionStatut::IN_PROGRESS);
            $actionItem->setDateUpdated(new \DateTime());
            $count++;
        }

        $em->flush();

        $this->addFlash('sonata_flash_success', sprintf('%d action item(s) marked as in progress.', $count));

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Batch action: Export action items report
     */
    public function batchActionExportReport(Request $request, EntityManagerInterface $em): Response
    {
        $selectedIds = $request->get('idx', []);

        if (empty($selectedIds)) {
            $this->addFlash('sonata_flash_error', 'No action items selected.');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $actionItems = $em->getRepository(ActionItem::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.reunion', 'r')
            ->leftJoin('a.responsable', 'p')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->orderBy('a.dateEcheance', 'ASC')
            ->getQuery()
            ->getResult();

        // Create CSV content
        $csv = $this->generateActionItemsCSV($actionItems);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="action_items_report_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    private function generateActionItemsCSV(array $actionItems): string
    {
        $output = fopen('php://temp', 'r+');

        // Headers
        fputcsv($output, [
            'ID',
            'Description',
            'Meeting',
            'Meeting Date',
            'Responsible Person',
            'Due Date',
            'Status',
            'Days Until Due',
            'Comments',
            'Created',
            'Last Updated'
        ]);

        // Data rows
        foreach ($actionItems as $item) {
            $daysUntilDue = null;
            if ($item->getDateEcheance()) {
                $now = new \DateTime();
                $dueDate = $item->getDateEcheance();
                $interval = $now->diff($dueDate);
                $daysUntilDue = $interval->invert ? -$interval->days : $interval->days;
            }

            fputcsv($output, [
                $item->getId(),
                $item->getDescription(),
                $item->getReunion() ? $item->getReunion()->getObjet() : '',
                $item->getReunion() && $item->getReunion()->getDateDebut() 
                    ? $item->getReunion()->getDateDebut()->format('Y-m-d H:i') 
                    : '',
                $item->getResponsable() ? $item->getResponsable()->getNomComplet() : '',
                $item->getDateEcheance() ? $item->getDateEcheance()->format('Y-m-d') : '',
                $item->getStatut() ? $item->getStatut()->label() : '',
                $daysUntilDue ?? 'N/A',
                $item->getCommentaire() ?? '',
                $item->getDateCreated() ? $item->getDateCreated()->format('Y-m-d H:i') : '',
                $item->getDateUpdated() ? $item->getDateUpdated()->format('Y-m-d H:i') : '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
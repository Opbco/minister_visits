<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MeetingRoom;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class MeetingRoomCRUDController extends CRUDController
{
    /**
     * Check room availability for a specific date/time range
     * This can be used for AJAX calls from meeting forms
     */
    public function checkAvailabilityAction(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $roomId = $request->query->get('room_id');
        $startTime = $request->query->get('start_time');
        $endTime = $request->query->get('end_time');

        if (!$roomId || !$startTime || !$endTime) {
            return new JsonResponse([
                'available' => false,
                'error' => 'Missing required parameters'
            ], 400);
        }

        try {
            $room = $em->getRepository(MeetingRoom::class)->find($roomId);
            
            if (!$room) {
                return new JsonResponse([
                    'available' => false,
                    'error' => 'Room not found'
                ], 404);
            }

            $start = new \DateTime($startTime);
            $end = new \DateTime($endTime);

            // Check for overlapping meetings
            $qb = $em->createQueryBuilder();
            $qb->select('COUNT(r.id)')
                ->from('App\Entity\Reunion', 'r')
                ->where('r.salle = :room')
                ->andWhere('r.dateDebut < :end')
                ->andWhere('r.dateFin > :start')
                ->setParameter('room', $room)
                ->setParameter('start', $start)
                ->setParameter('end', $end);

            $conflictCount = $qb->getQuery()->getSingleScalarResult();

            return new JsonResponse([
                'available' => $conflictCount === 0,
                'room_name' => $room->getNom(),
                'capacity' => $room->getCapacite(),
                'equipment' => $room->getEquipements(),
                'conflicts' => $conflictCount
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'available' => false,
                'error' => 'Error checking availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View room schedule/calendar
     */
    public function scheduleAction(Request $request): Response
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find Meeting Room with id: %s', $id));
        }

        if (!$object instanceof MeetingRoom) {
            throw new \RuntimeException('Invalid object type');
        }

        // Get meetings for this room, sorted by date
        $meetings = $object->getReunions()->toArray();
        usort($meetings, function($a, $b) {
            return $a->getDateDebut() <=> $b->getDateDebut();
        });

        return $this->renderWithExtraParams('@SonataAdmin/CRUD/meeting_room/schedule.html.twig', [
            'object' => $object,
            'meetings' => $meetings,
            'action' => 'show',
        ]);
    }

    /**
     * Export room details and schedule
     */
    public function exportScheduleAction(Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->get($this->admin->getIdParameter());
        $room = $em->getRepository(MeetingRoom::class)->find($id);

        if (!$room) {
            throw $this->createNotFoundException('Room not found');
        }

        $meetings = $room->getReunions()->toArray();
        usort($meetings, function($a, $b) {
            return $a->getDateDebut() <=> $b->getDateDebut();
        });

        // Create CSV content
        $csv = $this->generateScheduleCSV($room, $meetings);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', sprintf(
            'attachment; filename="room_schedule_%s_%s.csv"',
            str_replace(' ', '_', $room->getNom()),
            date('Y-m-d')
        ));

        return $response;
    }

    private function generateScheduleCSV(MeetingRoom $room, array $meetings): string
    {
        $output = fopen('php://temp', 'r+');

        // Room info header
        fputcsv($output, ['Room Schedule Report']);
        fputcsv($output, ['Room Name', $room->getNom()]);
        fputcsv($output, ['Capacity', $room->getCapacite() ?? 'N/A']);
        fputcsv($output, ['Location', $room->getStructure() ? $room->getStructure()->getNameFr() : 'N/A']);
        fputcsv($output, ['Equipment', implode(', ', $room->getEquipements() ?? [])]);
        fputcsv($output, []); // Empty row

        // Meeting schedule headers
        fputcsv($output, ['Meeting Title', 'Start Date', 'Start Time', 'End Time', 'Duration (min)', 'Organizer', 'Status']);

        // Meeting data
        foreach ($meetings as $meeting) {
            $duration = null;
            if ($meeting->getDateDebut() && $meeting->getDateFin()) {
                $diff = $meeting->getDateDebut()->diff($meeting->getDateFin());
                $duration = ($diff->h * 60) + $diff->i;
            }

            fputcsv($output, [
                $meeting->getObjet(),
                $meeting->getDateDebut()->format('Y-m-d'),
                $meeting->getDateDebut()->format('H:i'),
                $meeting->getDateFin() ? $meeting->getDateFin()->format('H:i') : '',
                $duration ?? '',
                $meeting->getOrganisateur() ? $meeting->getOrganisateur()->getNameFr() : '',
                $meeting->getStatut() ? $meeting->getStatut()->label() : '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
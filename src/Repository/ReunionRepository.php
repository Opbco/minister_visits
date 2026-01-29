<?php

namespace App\Repository;

use App\Entity\Personnel;
use App\Entity\Reunion;
use App\Entity\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reunion>
 *
 * @method Reunion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reunion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reunion[]    findAll()
 * @method Reunion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReunionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reunion::class);
    }

    /**
     * Find all reunions accessible to a specific personnel.
     * 
     * A reunion is accessible if:
     * 1. The personnel is a participant (via ReunionParticipation)
     * 2. The reunion is organized by the personnel's structure
     * 3. The personnel's structure codeHierarchique is part of the organizer's codeHierarchique
     *    (hierarchical relationship - e.g., "MINESEC/SDEC" is part of "MINESEC/SDEC/DRES")
     * 
     * @param Personnel $personnel The personnel to find reunions for
     * @return Reunion[] Returns an array of Reunion objects
     */
    public function findAccessibleByPersonnel(Personnel $personnel): array
    {
        $personnelStructure = $personnel->getStructure();

        if (!$personnelStructure) {
            // If personnel has no structure, only return reunions they directly participate in
            return $this->createQueryBuilder('r')
                ->innerJoin('r.participations', 'p')
                ->where('p.personnel = :personnel')
                ->setParameter('personnel', $personnel)
                ->orderBy('r.dateDebut', 'DESC')
                ->getQuery()
                ->getResult();
        }

        $personnelCodeHierarchique = $personnelStructure->getCodeHierarchique();

        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.participations', 'p')
            ->leftJoin('r.organisateur', 'org')
            ->where('p.personnel = :personnel') // Condition 1: Direct participation
            ->orWhere('org.id = :structureId')   // Condition 2: Same structure is organizer
            ->setParameter('personnel', $personnel)
            ->setParameter('structureId', $personnelStructure->getId());

        // Condition 3: Hierarchical relationship
        if (!empty($personnelCodeHierarchique)) {
            $qb->orWhere('org.codeHierarchique LIKE :codePattern')
                ->orWhere('org.codeHierarchique = :exactCode')
                ->setParameter('codePattern', $personnelCodeHierarchique . '/%')
                ->setParameter('exactCode', $personnelCodeHierarchique);
        }

        return $qb->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reunions where personnel is a direct participant.
     * 
     * @param Personnel $personnel
     * @return Reunion[] Returns an array of Reunion objects
     */
    public function findByParticipant(Personnel $personnel): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.participations', 'p')
            ->where('p.personnel = :personnel')
            ->setParameter('personnel', $personnel)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reunions organized by a specific structure.
     * 
     * @param Structure $structureId
     * @return Reunion[] Returns an array of Reunion objects
     */
    public function findByOrganisateur(Structure $structureId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.organisateur = :structure')
            ->setParameter('structure', $structureId)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reunions organized by structures in the same hierarchical tree.
     * 
     * @param string $codeHierarchique The hierarchical code to match
     * @return Reunion[] Returns an array of Reunion objects
     */
    public function findByHierarchicalCode(string $codeHierarchique): array
    {
        if (empty($codeHierarchique)) {
            return [];
        }

        return $this->createQueryBuilder('r')
            ->innerJoin('r.organisateur', 'org')
            ->where('org.codeHierarchique LIKE :codePattern')
            ->orWhere('org.codeHierarchique = :exactCode')
            ->setParameter('codePattern', $codeHierarchique . '/%')
            ->setParameter('exactCode', $codeHierarchique)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

}

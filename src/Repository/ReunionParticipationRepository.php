<?php

namespace App\Repository;

use App\Entity\ReunionParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReunionParticipation>
 *
 * @method ReunionParticipation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReunionParticipation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReunionParticipation[]    findAll()
 * @method ReunionParticipation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReunionParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReunionParticipation::class);
    }

    //    /**
    //     * @return ReunionParticipation[] Returns an array of ReunionParticipation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ReunionParticipation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

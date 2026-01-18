<?php

namespace App\Repository;

use App\Entity\ExternalParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExternalParticipant>
 *
 * @method ExternalParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExternalParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExternalParticipant[]    findAll()
 * @method ExternalParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExternalParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalParticipant::class);
    }

    //    /**
    //     * @return ExternalParticipant[] Returns an array of ExternalParticipant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ExternalParticipant
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

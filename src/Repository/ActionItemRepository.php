<?php

namespace App\Repository;

use App\Entity\ActionItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActionItem>
 *
 * @method ActionItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionItem[]    findAll()
 * @method ActionItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionItem::class);
    }

    //    /**
    //     * @return ActionItem[] Returns an array of ActionItem objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ActionItem
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

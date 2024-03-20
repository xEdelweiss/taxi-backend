<?php

namespace App\Repository;

use App\Entity\TripOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TripOrder>
 *
 * @method TripOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method TripOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method TripOrder[]    findAll()
 * @method TripOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripOrder::class);
    }

    //    /**
    //     * @return TripOrder[] Returns an array of TripOrder objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TripOrder
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

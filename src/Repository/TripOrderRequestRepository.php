<?php

namespace App\Repository;

use App\Entity\TripOrderRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TripOrderRequest>
 *
 * @method TripOrderRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method TripOrderRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method TripOrderRequest[]    findAll()
 * @method TripOrderRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripOrderRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripOrderRequest::class);
    }

//    /**
//     * @return TripOrderRequest[] Returns an array of TripOrderRequest objects
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

//    public function findOneBySomeField($value): ?TripOrderRequest
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

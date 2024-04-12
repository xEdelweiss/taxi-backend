<?php

namespace App\Repository;

use App\Entity\TripOrder;
use App\Entity\User;
use App\Service\Trip\Enum\TripStatus;
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

    /** @return TripOrder[] */
    public function findActiveOrders(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status not in (:statuses)')
            ->setParameter('statuses', [TripStatus::Completed, TripStatus::CanceledByDriver, TripStatus::CanceledByUser])
            ->getQuery()
            ->getResult();
    }

    /** @param TripStatus[]|null $statuses */
    public function findUserOrders(User $user, ?array $statuses): array
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user);

        if (!empty($statuses)) {
            $queryBuilder
                ->andWhere('o.status in (:statuses)')
                ->setParameter('statuses', $statuses);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /** @param TripStatus[]|null $statuses */
    public function findUserOrdersIncludingRequests(User $user, ?array $statuses = null): array
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $queryBuilder
            ->leftJoin('o.tripOrderRequest', 'tor')
            ->leftJoin('tor.driver', 'dp')
            ->leftJoin('dp.user', 'du')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('o.user', ':user'),
                    $queryBuilder->expr()->eq('du', ':user'),
                )
            )
            ->setParameter('user', $user);

        if (!empty($statuses)) {
            $queryBuilder
                ->andWhere('o.status in (:statuses)')
                ->setParameter('statuses', $statuses);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
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

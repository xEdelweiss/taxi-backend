<?php

namespace App\Repository;

use App\Entity\DriverProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DriverProfile>
 *
 * @method DriverProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method DriverProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method DriverProfile[]    findAll()
 * @method DriverProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriverProfile::class);
    }

    //    /**
    //     * @return DriverProfile[] Returns an array of DriverProfile objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DriverProfile
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

<?php

namespace App\Repository;

use App\Entity\DailyCountryChecksOnRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DailyCountryChecksOnRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyCountryChecksOnRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyCountryChecksOnRequest[]    findAll()
 * @method DailyCountryChecksOnRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyCountryChecksOnRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyCountryChecksOnRequest::class);
    }

    // /**
    //  * @return DailyCountryChecksOnRequest[] Returns an array of DailyCountryChecksOnRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DailyCountryChecksOnRequest
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

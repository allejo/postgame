<?php

namespace App\Repository;

use App\Entity\PartEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PartEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PartEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PartEvent[]    findAll()
 * @method PartEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PartEvent::class);
    }

    // /**
    //  * @return PartEvent[] Returns an array of PartEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PartEvent
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

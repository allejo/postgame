<?php

namespace App\Repository;

use App\Entity\ResumeEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ResumeEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResumeEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResumeEvent[]    findAll()
 * @method ResumeEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResumeEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResumeEvent::class);
    }

    // /**
    //  * @return ResumeEvent[] Returns an array of ResumeEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResumeEvent
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

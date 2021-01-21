<?php

namespace App\Repository;

use App\Entity\PlayerHeatMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerHeatMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerHeatMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerHeatMap[]    findAll()
 * @method PlayerHeatMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerHeatMapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerHeatMap::class);
    }

    // /**
    //  * @return PlayerHeatMap[] Returns an array of PlayerHeatMap objects
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
    public function findOneBySomeField($value): ?PlayerHeatMap
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

<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\KnownMap;
use App\Entity\MapThumbnail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MapThumbnail|null find($id, $lockMode = null, $lockVersion = null)
 * @method MapThumbnail|null findOneBy(array $criteria, array $orderBy = null)
 * @method MapThumbnail[]    findAll()
 * @method MapThumbnail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MapThumbnailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MapThumbnail::class);
    }

    public function findSingleThumbnailForMap(KnownMap $map): ?MapThumbnail
    {
        return $this->createQueryBuilder('t')
            ->join('t.knownMap', 'm')
            ->where('m = :map')
            ->setMaxResults(1)
            ->setParameter('map', $map)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

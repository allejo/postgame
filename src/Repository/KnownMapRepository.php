<?php

declare(strict_types=1);

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
 * @method null|KnownMap find($id, $lockMode = null, $lockVersion = null)
 * @method null|KnownMap findOneBy(array $criteria, array $orderBy = null)
 * @method KnownMap[]    findAll()
 * @method KnownMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KnownMapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KnownMap::class);
    }

    public function findThumbnails(): array
    {
        /** @var array<int, array{map_id: string, thumbnail_id: string}> $results */
        $results = $this->getEntityManager()->createQueryBuilder()
            ->select([
                'IDENTITY(t.knownMap) AS map_id',
                'MIN(t.id) AS thumbnail_id',
            ])
            ->from('App:MapThumbnail', 't')
            ->groupBy('t.knownMap')
            ->orderBy('thumbnail_id')
            ->getQuery()
            ->getScalarResult()
        ;

        $mapIDs = array_column($results, 'map_id');
        $thumbnailIDs = array_column($results, 'thumbnail_id');
        $thumbnails = $this->getEntityManager()->getRepository(MapThumbnail::class)
            ->findBy([
                'id' => $thumbnailIDs,
            ])
        ;

        return array_combine($mapIDs, $thumbnails);
    }

    /**
     * Find the number of replays that have taken place on each map.
     *
     * @return array<int, int> The key is KnownMap id where the value is the replay count
     */
    public function findUsageCounts(): array
    {
        /** @var array<int, array{map_id: string, replay_count: string}> $results */
        $results = $this->getEntityManager()->createQueryBuilder()
            ->select([
                'm.id AS map_id',
                'COUNT(m.id) AS replay_count',
            ])
            ->from('App:Replay', 'r')
            ->join('r.mapThumbnail', 't')
            ->join('t.knownMap', 'm')
            ->groupBy('m.id')
            ->getQuery()
            ->getResult()
        ;

        return array_combine(
            array_column($results, 'map_id'),
            array_map(static function ($count) {
                return (int)$count;
            }, array_column($results, 'replay_count'))
        );
    }
}

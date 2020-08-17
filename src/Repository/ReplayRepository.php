<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\KnownMap;
use App\Entity\Replay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @method Replay|null find($id, $lockMode = null, $lockVersion = null)
 * @method Replay|null findOneBy(array $criteria, array $orderBy = null)
 * @method Replay[]    findAll()
 * @method Replay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReplayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Replay::class);
    }

    public function findByMap(KnownMap $map, int $limit = null, \DateTime $after = null): array
    {
        $query = $this->createQueryBuilder('r')
            ->join('r.mapThumbnail', 't')
            ->join('t.knownMap', 'm')
            ->where('m = :map')
            ->setParameter('map', $map)
            ->setMaxResults($limit)
            ->orderBy('r.startTime', 'DESC')
        ;

        if ($after !== null) {
            $query
                ->where('r.startTime < :after')
                ->setParameter('after', $after->format(DATE_ATOM))
            ;
        }

        return $query->getQuery()->getResult();
    }

    public function findNewest(): ?Replay
    {
        try {
            return $this->createQueryBuilder('r')
                ->orderBy('r.startTime', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findOldest(): ?Replay
    {
        try {
            return $this->createQueryBuilder('r')
                ->orderBy('r.startTime', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @return Replay[]
     */
    public function findByTimeRange(?\DateTime $after = null, ?\DateTime $before = null): array
    {
        $flipRequired = false;
        $qb = $this->createQueryBuilder('r');
        $qb->orderBy('r.startTime', 'DESC');

        if ($after !== null) {
            $qb
                ->andWhere('r.startTime < :after')
                ->setParameter('after', $after->format(DATE_ATOM))
            ;
        }

        if ($before !== null) {
            $qb
                ->andWhere('r.startTime > :before')
                ->setParameter('before', $before->format(DATE_ATOM))
                ->orderBy('r.startTime', 'ASC')
            ;

            $flipRequired = true;
        }

        $qb->setMaxResults(20);

        $result = $qb->getQuery()->getResult();

        // We need to manually flip the results via PHP when using the `before`
        // value because we need to get our results as close as possible to the
        // given datetime, which we must do in ASC order.
        if ($flipRequired) {
            $result = array_reverse($result);
        }

        return $result;
    }

    /**
     * Fetch a summary of how many matches occurred on a given day between the
     * given dates of `$start` and `$end`; optionally filtered by `$map`.
     *
     * - If null is given for `$start`, then -90 days from today.
     * - If null is given for `$end`, the current day is used.
     *
     * This method returns an array of array shapes; the `match_date` is a
     * string in the format of `YYYY-MM-DD` and `match_count` is a string with
     * the number of matches that occurred on that day.
     *
     * @return array<array{match_date: string, match_count: string}>
     */
    public function getSummaryCount(?\DateTime $start = null, ?\DateTime $end = null, ?KnownMap $map = null): array
    {
        $end = $end ?? new \DateTime('now');
        $start = $start ?? (new \DateTime('now'))->modify('-90 days');

        $qb = $this->createQueryBuilder('r');
        $qb
            ->select("DATE_FORMAT(r.startTime, '%Y-%m-%d') AS match_date, COUNT(r.id) AS match_count")
            ->andWhere('r.startTime >= :start')
            ->setParameter('start', $start->format(DATE_ATOM))
            ->andWhere('r.startTime <= :end')
            ->setParameter('end', $end->format(DATE_ATOM))
            ->orderBy('match_date', 'ASC')
            ->groupBy('match_date')
        ;

        if ($map !== null) {
            $qb
                ->join('r.mapThumbnail', 't')
                ->join('t.knownMap', 'm')
                ->andWhere('m = :map')
                ->setParameter('map', $map)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the number of replays that have taken a part on this map.
     */
    public function getMapUsageCount(KnownMap $map): int
    {
        try {
            return (int)$this->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->join('r.mapThumbnail', 't')
                ->join('t.knownMap', 'm')
                ->where('m = :map')
                ->setParameter('map', $map)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (NonUniqueResultException | NoResultException $e) {
            return 0;
        }
    }
}

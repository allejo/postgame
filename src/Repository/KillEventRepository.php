<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\KillEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method KillEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method KillEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method KillEvent[]    findAll()
 * @method KillEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KillEventRepository extends ServiceEntityRepository
{
    use DateRangeTrait;
    use DeletableReplayTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, KillEvent::class);
    }

    public function findTopKillers(int $count = 10, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('ke');
        $qb
            ->select('k.callsign, COUNT(ke.victim) as kill_count')
            ->join('ke.killer', 'k')
            ->join('ke.replay', 'r')
            ->where('ke.killer != ke.victim')
            ->orderBy('kill_count', 'DESC')
            ->groupBy('k.callsign')
            ->setMaxResults($count)
        ;

        $this->applyDateRangeToQueryBuilder($qb, 'r', $start, $end);

        return $qb->getQuery()->getResult();
    }

    public function findTopVictims(int $count = 10, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('ke');
        $qb
            ->select('v.callsign, COUNT(ke.killer) AS death_count')
            ->join('ke.victim', 'v')
            ->join('ke.replay', 'r')
            ->where('ke.killer != ke.victim')
            ->orderBy('death_count', 'DESC')
            ->groupBy('v.callsign')
            ->setMaxResults($count)
        ;

        $this->applyDateRangeToQueryBuilder($qb, 'r', $start, $end);

        return $qb->getQuery()->getResult();
    }
}

<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\CaptureEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CaptureEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaptureEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaptureEvent[]    findAll()
 * @method CaptureEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaptureEventRepository extends ServiceEntityRepository
{
    use DateRangeTrait;
    use DeletableReplayTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CaptureEvent::class);
    }

    public function findTopCappers(int $count = 10, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('ce');
        $qb
            ->select('p.callsign, COUNT(ce.id) AS cap_count')
            ->join('ce.capper', 'p')
            ->join('ce.replay', 'r')
            ->groupBy('p.callsign')
            ->orderBy('cap_count', 'DESC')
            ->setMaxResults($count)
        ;

        $this->applyDateRangeToQueryBuilder($qb, 'r', $start, $end);

        return $qb->getQuery()->getResult();
    }
}

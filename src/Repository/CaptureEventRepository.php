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
    use DeletableReplayTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CaptureEvent::class);
    }

    public function findTopCappers(int $count = 15, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $start = $start ?? new \DateTime('now');

        if ($end === null) {
            $end = new \DateTime('now');
            $end->modify('-90 days');
        }

        $qb = $this->createQueryBuilder('ce');
        $qb
            ->select('p.callsign, COUNT(ce.id) AS cap_count')
            ->join('ce.capper', 'p')
            ->join('ce.replay', 'r')

            ->groupBy('p.callsign')

            ->andWhere('r.startTime <= :start')
            ->setParameter('start', $start->format(DATE_ATOM))
            ->andWhere('r.startTime >= :end')
            ->setParameter('end', $end->format(DATE_ATOM))

            ->orderBy('cap_count', 'DESC')
            ->setMaxResults($count)
        ;

        return $qb->getQuery()->getResult();
    }
}

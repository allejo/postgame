<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Replay;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Replay|null find($id, $lockMode = null, $lockVersion = null)
 * @method Replay|null findOneBy(array $criteria, array $orderBy = null)
 * @method Replay[]    findAll()
 * @method Replay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReplayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Replay::class);
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
     * @param DateTime|null $after
     * @param DateTime|null $before
     *
     * @return Replay[]
     */
    public function findByTimeRange(?DateTime $after = null, ?DateTime $before = null): array
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

    public function getSummaryCount(?DateTime $start = null, ?DateTime $end = null)
    {
        $end = $end ?? new DateTime('now');
        $start = $start ?? (new DateTime('now'))->modify('-90 days');

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

        return $qb->getQuery()->getResult();
    }
}

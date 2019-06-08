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

    /**
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return Replay[]
     */
    public function findByTimeRange(?DateTime $start, ?DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('r');

        if ($start !== null) {
            $qb
                ->andWhere('r.startTime < :start')
                ->setParameter('start', $start)
            ;
        }

        if ($end !== null) {
            $qb
                ->andWhere('r.startTime < :end')
                ->setParameter('end', $end)
            ;
        }

        return $qb
            ->orderBy('r.startTime', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }
}

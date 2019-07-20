<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    use DateRangeTrait;
    use DeletableReplayTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findMostActive(int $count = 10, ?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('p.callsign, COUNT(p.replay) AS match_count')
            ->join('p.replay', 'r')
            ->groupBy('p.callsign')
            ->orderBy('match_count', 'DESC')
            ->setMaxResults($count)
        ;

        $this->applyDateRangeToQueryBuilder($qb, 'r', $start, $end);

        return $qb->getQuery()->getResult();
    }
}

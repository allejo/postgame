<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\FlagUpdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FlagUpdate|null find($id, $lockMode = null, $lockVersion = null)
 * @method FlagUpdate|null findOneBy(array $criteria, array $orderBy = null)
 * @method FlagUpdate[]    findAll()
 * @method FlagUpdate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlagUpdateRepository extends ServiceEntityRepository
{
    use DeletableReplayTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FlagUpdate::class);
    }
}

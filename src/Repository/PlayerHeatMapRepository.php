<?php
declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\PlayerHeatMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerHeatMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerHeatMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerHeatMap[]    findAll()
 * @method PlayerHeatMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerHeatMapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerHeatMap::class);
    }
}

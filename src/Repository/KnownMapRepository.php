<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\KnownMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method KnownMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method KnownMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method KnownMap[]    findAll()
 * @method KnownMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KnownMapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KnownMap::class);
    }
}

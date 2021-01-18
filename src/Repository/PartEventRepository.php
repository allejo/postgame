<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\PartEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|PartEvent find($id, $lockMode = null, $lockVersion = null)
 * @method null|PartEvent findOneBy(array $criteria, array $orderBy = null)
 * @method PartEvent[]    findAll()
 * @method PartEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartEventRepository extends ServiceEntityRepository
{
    use DeletableReplayTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartEvent::class);
    }
}

<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\PauseEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|PauseEvent find($id, $lockMode = null, $lockVersion = null)
 * @method null|PauseEvent findOneBy(array $criteria, array $orderBy = null)
 * @method PauseEvent[]    findAll()
 * @method PauseEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PauseEventRepository extends ServiceEntityRepository
{
    use DeletableReplayTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PauseEvent::class);
    }
}

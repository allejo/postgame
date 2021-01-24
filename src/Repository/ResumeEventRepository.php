<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\ResumeEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|ResumeEvent find($id, $lockMode = null, $lockVersion = null)
 * @method null|ResumeEvent findOneBy(array $criteria, array $orderBy = null)
 * @method ResumeEvent[]    findAll()
 * @method ResumeEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResumeEventRepository extends ServiceEntityRepository
{
    use DeletableReplayTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResumeEvent::class);
    }
}

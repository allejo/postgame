<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\JoinEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JoinEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method JoinEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method JoinEvent[]    findAll()
 * @method JoinEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JoinEventRepository extends ServiceEntityRepository
{
    use DeletableReplayTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JoinEvent::class);
    }
}

<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Replay;

trait DeletableReplayTrait
{
    public function deleteByReplay(Replay $replay)
    {
        return $this->createQueryBuilder('en')
            ->delete()
            ->where('en.replay = :replay')
            ->setParameter('replay', $replay)
            ->getQuery()
            ->execute()
        ;
    }
}

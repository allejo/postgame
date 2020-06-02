<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

trait DateRangeTrait
{
    /**
     * Apply WHERE conditions to the alias for the Replay join table.
     */
    protected function applyDateRangeToQueryBuilder(QueryBuilder $qb, string $replayAlias, ?\DateTime $start, ?\DateTime $end): void
    {
        if (!in_array($replayAlias, $qb->getAllAliases())) {
            return;
        }

        try {
            $start = $start ?? new \DateTime('now');
            $end = $end ?? (new \DateTime('now'))->modify('-90 days');

            $qb
                ->andWhere("$replayAlias.startTime <= :start")
                ->setParameter('start', $start->format(DATE_ATOM))
                ->andWhere("$replayAlias.startTime >= :end")
                ->setParameter('end', $end->format(DATE_ATOM))
            ;
        } catch (\Exception $e) {
        }
    }
}

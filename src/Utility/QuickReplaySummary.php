<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

use App\Entity\Replay;
use App\Service\ReplaySummaryService;

class QuickReplaySummary
{
    /**
     * The duration of the replay in minutes.
     *
     * @var int
     */
    public $duration;

    /**
     * The numerical representation of the winning team.
     *
     * @var int
     *
     * @see BZTeamType
     */
    public $winner;

    /**
     * The score of the winning team.
     *
     * @var int
     */
    public $winnerScore;

    /**
     * The numerical representation of the losing team.
     *
     * @var int
     *
     * @see BZTeamType
     */
    public $loser;

    /**
     * The score of the losing team.
     *
     * @var int
     */
    public $loserScore;

    /**
     * @param ReplaySummaryService $service  The summary service that typically comes via DI
     * @param Replay[]             $replays
     * @param null|callable(Replay $replay): void $callback
     * @param null|callable(Replay $replay,  UnsummarizedException|WrongSummarizationException $e): void $onError
     *
     * @return QuickReplaySummary[]
     */
    public static function summarizeReplays(ReplaySummaryService $service, array $replays, ?callable $callback = null, ?callable $onError = null): array
    {
        /** @var QuickReplaySummary[] $summaries */
        $summaries = [];

        foreach ($replays as $replay) {
            if ($callback) {
                $callback($replay);
            }

            $service->summarizeQuick($replay);

            try {
                $summary = new self();
                $summary->duration = $service->getDuration();
                $summary->winner = $service->getWinner();
                $summary->winnerScore = $service->getWinnerScore();
                $summary->loser = $service->getLoser();
                $summary->loserScore = $service->getLoserScore();

                $summaries[$replay->getId()] = $summary;
            } catch (UnsummarizedException | WrongSummarizationException $e) {
                if ($onError) {
                    $onError($replay, $e);
                }
            }
        }

        return $summaries;
    }
}

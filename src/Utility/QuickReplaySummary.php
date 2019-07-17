<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

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
}

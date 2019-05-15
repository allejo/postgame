<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class SummaryPlayerRecord
{
    /** @var string */
    public $callsign;

    /**
     * @see BZTeamType
     *
     * @var int
     */
    public $team;

    /** @var string */
    public $teamLiteral;

    /** @var SummaryScoreCard */
    public $score;

    /** @var array<int, SummaryVersusCard> */
    public $against;

    /** @var SummaryKillRecord[] */
    public $kills = [];

    /** @var SummaryDeathRecord[] */
    public $deaths = [];

    /** @var SummarySession[] */
    public $sessions = [];

    /** @var SummaryCaptureRecord[] */
    public $flagCaptures = [];

    public function __construct()
    {
        $this->score = new SummaryScoreCard();
        $this->against = new DefaultArray(SummaryVersusCard::class);
    }
}

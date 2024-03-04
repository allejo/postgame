<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class SummaryCaptureRecord
{
    /** @var int */
    public $playerId;

    /**
     * The int value of the team that captured the flag.
     *
     * @see BZTeamType
     *
     * @var int
     */
    public $cappingTeam;

    /**
     * The new score of the team tha capped this flag.
     *
     * @var int
     */
    public $cappingTeamScore;

    /**
     * The int value of the team that had their flag captured.
     *
     * @see BZTeamType
     *
     * @var int
     */
    public $cappedTeam;

    /**
     * The score of the team that had their flag captured.
     *
     * @var int
     */
    public $cappedTeamScore;

    /** @var string */
    public $matchTime;

    /** @var \DateTime */
    public $timestamp;
}

<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class SummaryChatMessage
{
    /** @var int */
    public $sender;

    /**
     * @see BZTeamType
     *
     * @var int
     */
    public $senderTeam;

    /**
     * The int value of the player/team this message was sent to.
     *
     * @see BZChatTarget
     *
     * @var int
     */
    public $recipient;

    /** @var string */
    public $message;

    /** @var string */
    public $matchTime;

    /** @var \DateTime */
    public $timestamp;
}

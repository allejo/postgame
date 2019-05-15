<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class SummarySession
{
    /** @var int */
    public $team;

    /** @var int */
    public $ipAddress;

    /** @var \DateTime */
    public $joinTime;

    /** @var \DateTime|null */
    public $partTime;

    /** @var int Total time of this session in seconds */
    public $totalTime;
}

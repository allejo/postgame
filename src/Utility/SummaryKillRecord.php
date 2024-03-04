<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class SummaryKillRecord
{
    /** @var int */
    public $victim;

    /** @var string */
    public $matchTime;

    /** @var \DateTime */
    public $timestamp;
}

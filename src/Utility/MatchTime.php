<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class MatchTime
{
    /** @var int */
    public $hours;

    /** @var int */
    public $minutes;

    /** @var int */
    public $seconds;

    public function __construct(int $seconds)
    {
        $hourLen = 60 * 60;
        $this->hours = (int)($seconds / $hourLen);
        $seconds = $seconds % $hourLen;

        $minutesLen = 60;
        $this->minutes = (int)($seconds / $minutesLen);
        $seconds = $seconds % $minutesLen;

        $this->seconds = (int)$seconds;
    }

    public function __toString(): string
    {
        $hours = '';

        if ($this->hours > 0) {
            $hours = sprintf('%02d:', $this->hours);
        }

        return sprintf('%s%02d:%02d', $hours, $this->minutes, $this->seconds);
    }
}

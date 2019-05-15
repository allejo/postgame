<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

abstract class BZChatTarget
{
    const PUBLIC = 254;
    const ADMIN = 252;
    const ROGUE = 251;
    const RED = 250;
    const GREEN = 249;
    const BLUE = 248;
    const PURPLE = 247;
    const OBSERVER = 246;
    const RABBIT = 245;
    const HUNTER = 244;

    const LAST_PLAYER = self::HUNTER - 1;
}

<?php

declare(strict_types=1);

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

    private static $strings = [
        self::PUBLIC => 'public',
        self::ADMIN => 'admin',
        self::ROGUE => 'rogue',
        self::RED => 'red',
        self::GREEN => 'green',
        self::BLUE => 'blue',
        self::PURPLE => 'purple',
        self::OBSERVER => 'observer',
        self::RABBIT => 'rabbit',
        self::HUNTER => 'hunter',
    ];

    public static function toString(int $code): string
    {
        return self::$strings[$code] ?? "Player ID #{$code}";
    }
}

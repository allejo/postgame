<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

abstract class BZTeamType
{
    const ROGUE = 0;
    const RED = 1;
    const GREEN = 2;
    const BLUE = 3;
    const PURPLE = 4;
    const OBSERVER = 5;
    const RABBIT = 6;
    const HUNTER = 7;

    private static $strings = [
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
        return self::$strings[$code] ?? 'unknown';
    }
}
